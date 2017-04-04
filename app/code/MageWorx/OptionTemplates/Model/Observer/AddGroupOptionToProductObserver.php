<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\Observer;

use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory as ProductOptionCollectionFactory;
use Magento\Framework\Event\Observer;
use MageWorx\OptionTemplates\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;

/**
 * Observer class for add option groups to product
 */
class AddGroupOptionToProductObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var  \Magento\Framework\Registry
     */
    protected $registry;

    /**
     *
     * @var \MageWorx\OptionTemplates\Model\OptionSaver
     */
    protected $optionSaver;

    /**
     *
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     *
     * @var ProductOptionCollectionFactory
     */
    protected $productOptionCollectionFactory;

    /**
     *
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\OptionTemplates\Model\OptionSaver $optionSaver
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param ProductOptionCollectionFactory $productOptionCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageWorx\OptionTemplates\Model\OptionSaver $optionSaver,
        GroupCollectionFactory $groupCollectionFactory,
        ProductOptionCollectionFactory $productOptionCollectionFactory
    ) {
        $this->registry = $registry;
        $this->optionSaver = $optionSaver;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->productOptionCollectionFactory = $productOptionCollectionFactory;
    }

    /**
     *
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getRequest();
        $productId = $request->getParam('id');
        $post = $request->getPostValue();

        if ($this->_out($request)) {
            return;
        }

        if (empty($post['product']['options'])) {
            $productOptions = [];
        } else {
            $productOptions = $this->restoreGroupOptionIds($post['product']['options'], $productId);
        }

        if (!isset($post['product']['option_groups']) || !is_array($post['product']['option_groups'])) {
            $post['product']['option_groups'] = [];
            $postGroupIds = [];
        } else {
            $postGroupIds = $post['product']['option_groups'];
        }

        $modProductOptions = $productOptions;

        /** @var \MageWorx\OptionTemplates\Model\ResourceModel\Group\Collection $collectionByProduct */
        $collectionByProduct = $this->groupCollectionFactory->create();
        $issetGroupIds = $productId ? $collectionByProduct->addProductFilter($productId)->getAllIds() : [];
        $issetGroupIds = array_map('strval', $issetGroupIds);

        $addedGroupIds = array_diff($postGroupIds, $issetGroupIds);
        $deletedGroupIds = array_diff($issetGroupIds, $postGroupIds);

        $groupIds = array_merge($addedGroupIds + $deletedGroupIds);

        if ($groupIds) {
            /** @var \MageWorx\OptionTemplates\Model\ResourceModel\Group\Collection $collection */
            $collection = $this->groupCollectionFactory->create()->addFieldToFilter('group_id', $groupIds);
            /** @var \MageWorx\OptionTemplates\Model\Group $group */
            foreach ($collection as $group) {
                if (in_array($group->getId(), $addedGroupIds)) {
                    $modProductOptions = $this->optionSaver->addNewOptionProcess($modProductOptions, $group);
                }
                if (in_array($group->getId(), $deletedGroupIds)) {
                    $modProductOptions = $this->optionSaver->deleteOptionProcess($modProductOptions, $group);
                }
            }
        }

        $registryIds = [
            'productId' => $productId,
            'newGroupIds' => $addedGroupIds,
            'delGroupIds' => $deletedGroupIds,
        ];

        $this->registry->register('mageworx_optiontemplates_relation_data', $registryIds, true);

        $post['product']['options'] = $modProductOptions;
        $request->setPostValue($post);
    }

    /**
     * Check if go out
     *
     * @param $request
     * @return bool
     */
    protected function _out($request)
    {
        if (!in_array($request->getFullActionName(), $this->_getAvailableActions())) {
            return true;
        }

        $isCanSaveOptions = isset($request->getPost('product')['affect_product_custom_options']);

        if (!$isCanSaveOptions) {
            return true;
        }

        return false;
    }

    /**
     * Add group_option_id to product option post data
     * @todo modify option templates for avoid it
     *
     * @param array $postOptions
     * @param int $productId
     * @return array
     */
    protected function restoreGroupOptionIds($postOptions, $productId)
    {
        $productOptionCollection = $this->productOptionCollectionFactory->create();
        $productOptionCollection->addProductToFilter($productId);

        foreach ($productOptionCollection as $productOption) {
            if (!empty($productOption['group_option_id'])) {
                if (!empty($postOptions[$productOption->getId()])) {
                    $postOptions[$productOption->getId()]['group_option_id'] = $productOption['group_option_id'];
                }
            }
        }

        return $postOptions;
    }

    /**
     * Retrieve list of available actions
     *
     * @return array
     */
    protected function _getAvailableActions()
    {
        return ['catalog_product_save'];
    }
}
