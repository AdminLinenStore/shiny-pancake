<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Controller\Adminhtml\Group;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\OptionTemplates\Controller\Adminhtml\Group as GroupController;
use MageWorx\OptionTemplates\Model\Group\Source\AssignType;
use MageWorx\OptionTemplates\Model\OptionSaver;

class Save extends GroupController
{
    /**
     *
     * @var OptionSaver
     */
    protected $optionSaver;

    /**
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     *
     * @var JsHelper
     */
    protected $jsHelper;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        OptionSaver $optionSaver,
        JsHelper $jsHelper,
        \MageWorx\OptionTemplates\Controller\Adminhtml\Group\Builder $groupBuilder,
        Context $context
    ) {
        $this->optionSaver = $optionSaver;
        $this->jsHelper = $jsHelper;
        $this->productCollectionFactory = $productCollectionFactory;

        parent::__construct($groupBuilder, $context);
    }

    /**
     * Run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('mageworx_optiontemplates_group');

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->filterData($data);
            /** @var \MageWorx\OptionTemplates\Model\Group $group */
            $group = $this->groupBuilder->build($this->getRequest());

            $lastProductOptionArray = $group->getOptionArray();

            /**
             * Initialize product options
             */
            if (isset($data['options']) && !$group->getOptionsReadonly()) {
                $options = $this->mergeProductOptions(
                    $data['options'],
                    $this->_request->getPost('options_use_default')
                );
                $group->setProductOptions($options);
            }

            $group->addData($data);
            $group->setCanSaveCustomOptions(
                (bool)$group->getData('affect_product_custom_options') && !$group->getOptionsReadonly()
            );

            /**
             * Initialize product relation
             */
            $productIds = $this->getProductIds($data);
            $group->setProductsIds($productIds);

            $this->_eventManager->dispatch(
                'mageworx_optiontemplates_group_prepare_save',
                [
                    'group' => $group,
                    'request' => $this->getRequest(),
                ]
            );

            try {
                $group->save();
                $this->optionSaver->saveProductOptions($group, $lastProductOptionArray);
                $this->messageManager->addSuccessMessage(__('The options template has been saved.'));
                $this->_getSession()->setMageWorxOptionTemplatesGroupData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'mageworx_optiontemplates/*/edit',
                        [
                            'group_id' => $group->getId(),
                            '_current' => true,
                        ]
                    );

                    return $resultRedirect;
                }
                $resultRedirect->setPath('mageworx_optiontemplates/*/');

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the options template.'));
            }

            $this->_getSession()->setMageWorxOptionTemplatesGroupData($data);
            $resultRedirect->setPath(
                'mageworx_optiontemplates/*/edit',
                [
                    'group_id' => $group->getId(),
                    '_current' => true,
                ]
            );

            return $resultRedirect;
        }

        $resultRedirect->setPath('mageworx_optiontemplates/*/');

        return $resultRedirect;
    }

    /**
     * Merge product and default options for product
     *
     * @param array $productOptions product options
     * @param array $overwriteOptions default value options
     * @return array
     */
    protected function mergeProductOptions($productOptions, $overwriteOptions)
    {
        if (!is_array($productOptions)) {
            $productOptions = [];
        }
        if (is_array($overwriteOptions)) {
            $options = array_replace_recursive($productOptions, $overwriteOptions);
            array_walk_recursive($options, function (&$item) {
                if ($item === "") {
                    $item = null;
                }
            });
        } else {
            $options = $productOptions;
        }

        return $options;
    }

    /**
     *
     * @param array $data
     * @return array
     */
    protected function getProductIds($data)
    {
        $productIds = [];

        if ($data['assign_type'] == AssignType::ASSIGN_BY_GRID) {
            $productIds = isset($data['products']) ? $data['products'] : [];
        } else if ($data['assign_type'] == AssignType::ASSIGN_BY_IDS) {
            $productIds = $this->convertMultiStringToArray($data['productids'], 'intval');
        } else if ($data['assign_type'] == AssignType::ASSIGN_BY_SKUS) {
            $productSkus = $this->convertMultiStringToArray($data['productskus']);

            if ($productSkus) {
                /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
                $collection = $this->productCollectionFactory->create();
                $collection->addFieldToFilter('sku', ['in' => $productSkus]);
                $productIds = array_map('intval', $collection->getAllIds());
            }
        }

        return $productIds;
    }

    /**
     *
     * @param string $string
     * @param string $finalFunction
     * @return array
     */
    protected function convertMultiStringToArray($string, $finalFunction = null)
    {
        if (!trim($string)) {
            return [];
        }

        $rawLines = array_filter(preg_split('/\r?\n/', $string));
        $rawLines = array_map('trim', $rawLines);
        $lines = array_filter($rawLines);

        if (!$lines) {
            return [];
        }

        $array = [];
        foreach ($lines as $line) {
            $rawIds = explode(',', $line);
            $rawIds = array_map('trim', $rawIds);
            $lineIds = array_filter($rawIds);
            if (!$finalFunction) {
                $lineIds = array_map($finalFunction, $lineIds);
            }
            $array = array_merge($array, $lineIds);
        }

        return $array;
    }

    protected function filterData($data)
    {
        if (isset($data['group_id']) && !$data['group_id']) {
            unset($data['group_id']);
        }

        if (isset($data['options'])) {
            $updatedOptions = [];
            foreach ($data['options'] as $key => $option) {
                if (!isset($option['option_id'])) {
                    continue;
                }

                $optionId = $option['option_id'];
                if (!$optionId && !empty($option['record_id'])) {
                    $optionId = $option['record_id'] . '_';
                }
                $updatedOptions[$optionId] = $option;
                if (empty($option['values'])) {
                    continue;
                }

                foreach ($option['values'] as $valueKey => $value) {
                    if (!isset($value['option_type_id'])) {
                        continue;
                    }
                    $valueId = $value['option_type_id'];
                    $updatedOptions[$optionId]['values'][$valueId] = $value;
                    unset($updatedOptions[$optionId]['values'][$valueKey]);
                }
            }

            $data['options'] = $updatedOptions;
        }

        return $data;
    }
}
