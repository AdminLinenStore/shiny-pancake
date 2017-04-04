<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\Group;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product;

/**
 * Catalog group option model
 *
 * @method \Magento\Catalog\Model\ResourceModel\Product\Option getResource()
 * @method int getProductId()
 * @method \Magento\Catalog\Model\Product\Option setProductId(int $value)
 *
 */
class Option extends \Magento\Catalog\Model\Product\Option implements ProductCustomOptionInterface
{
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param Option\Value $productOptionValue
     * @param \Magento\Catalog\Model\Product\Option\Type\Factory $optionFactory
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\Option\Validator\Pool $validatorPool
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \MageWorx\OptionTemplates\Model\Group\Option\Value $productOptionValue,
        \Magento\Catalog\Model\Product\Option\Type\Factory $optionFactory,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\Option\Validator\Pool $validatorPool,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productOptionValue,
            $optionFactory,
            $string,
            $validatorPool,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageWorx\OptionTemplates\Model\ResourceModel\Group\Option');
    }

    /**
     * Save options.
     *
     * @return $this
     */
    public function saveOptions()
    {
        foreach ($this->getOptions() as $option) {
            // Clear stored data
            $this->storedData = [];
            // Manually delete is_deleted values from the option
            if (isset($option['values'])) {
                foreach ($option['values'] as $valueId => $value) {
                    $this->productOptionValue->deleteValues($valueId);
                }
            }

            $this->_validatorBeforeSave = null;
            $this->setData(
                $option
            )->setData(
                'group_id',
                $this->getProduct()->getId()
            )->setData(
                'store_id',
                $this->getProduct()->getStoreId()
            );
            /** Reset is delete flag from the previous iteration */
            $this->isDeleted(false);

            if ($this->getData('option_id') == '0' || !$this->getData('option_id')) {
                $this->unsetData('option_id');
            } else {
                $this->setId($this->getData('option_id'));
            }
            $isEdit = (bool)$this->getId();

            if ($this->getData('is_delete') == '1') {
                if ($isEdit) {
                    $this->getValueInstance()->deleteValue($this->getId());
                    $this->deleteTitles($this->getId());
                    $this->deletePrices($this->getId());
                    $this->_getResource()->delete($this);
                }
            } else {
                if ($this->getData('previous_type') != '') {
                    $previousType = $this->getData('previous_type');

                    /**
                     * if previous option has different group from one is came now need to remove all data of previous group
                     */
                    if ($this->getGroupByType($previousType) != $this->getGroupByType($this->getData('type'))) {
                        switch ($this->getGroupByType($previousType)) {
                            case self::OPTION_GROUP_SELECT:
                                $this->unsetData('values');
                                if ($isEdit) {
                                    $this->getValueInstance()->deleteValue($this->getId());
                                }
                                break;
                            case self::OPTION_GROUP_FILE:
                                $this->setData('image_size_x', '0');
                                $this->setData('image_size_y', '0');
                                $this->setData('file_extension', '');
                                break;
                            case self::OPTION_GROUP_DATE:
                                break;
                            case self::OPTION_GROUP_TEXT:
                                $this->setData('max_characters', '0');
                                break;
                        }

                        if ($this->getGroupByType($this->getData('type')) == self::OPTION_GROUP_SELECT) {
                            $this->unsetData('price');
                            $this->unsetData('price_type');
                            $this->setData('sku', '');
                            if ($isEdit) {
                                $this->deletePrices($this->getId());
                            }
                        }
                    }
                }

                $this->_getResource()->save($this);
            }
        }

        return $this;
    }

    /**
     * Get Product Option Collection
     *
     * @param Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
     */
    public function getProductOptionCollection(Product $product)
    {
        /** @var \MageWorx\OptionTemplates\Model\ResourceModel\Group\Option\Collection $collection */
        $collection = clone $this->getCollection();
        $collection->addFieldToFilter(
            'group_id',
            $product->getId()
        )->addTitleToResult(
            $product->getStoreId()
        )->addPriceToResult(
            $product->getStoreId()
        )->setOrder(
            'sort_order',
            'asc'
        )->setOrder(
            'title',
            'asc'
        );

        if ($this->getAddRequiredFilter()) {
            $collection->addRequiredFilter($this->getAddRequiredFilterValue());
        }

        $collection->addValuesToResult($product->getStoreId());

        return $collection;
    }
}
