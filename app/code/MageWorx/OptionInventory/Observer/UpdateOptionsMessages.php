<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use \Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class ModifyPriceConfiguration.
 * This observer add stock message to options which type are select|multiselect
 *
 * @package MageWorx\OptionInventory\Observer
 */
class UpdateOptionsMessages implements ObserverInterface
{
    /**
     * Option Value Collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     */
    protected $valueCollection;

    /**
     * OptionInventory Helper Data
     *
     * @var \MageWorx\OptionInventory\Helper\Data
     */
    protected $helperData;

    /**
     * OptionInventory Stock Data
     *
     * @var \MageWorx\OptionInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * Product custom option model
     *
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $optionModel;

    /**
     * ModifyPriceConfiguration constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection $valueCollection
     * @param \MageWorx\OptionInventory\Helper\Data $helperData
     * @param \MageWorx\OptionInventory\Helper\Stock $stockHelper
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection $valueCollection,
        \MageWorx\OptionInventory\Helper\Data $helperData,
        \MageWorx\OptionInventory\Helper\Stock $stockHelper,
        \Magento\Catalog\Model\Product\Option $optionModel
    ) {
    
        $this->valueCollection = $valueCollection;
        $this->helperData = $helperData;
        $this->stockHelper = $stockHelper;
        $this->optionModel = $optionModel;
    }

    /**
     * @param EventObserver $observer
     * @return mixed
     */
    public function execute(EventObserver $observer)
    {
        $configObj = $observer->getEvent()->getData('configObj');
        $options = $configObj->getData('config');
        $optionValuesId = $this->stockHelper->getOptionValuesId($options);
        $optionValuesCollection = $this->valueCollection->getValuesByOption($optionValuesId);

        foreach ($options as $optionId => $values) {
            $option = $this->optionModel->load($optionId);
            if ($option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT) {
                foreach ($values as $valueId => $valueData) {
                    $value = $optionValuesCollection->getItemById($valueId);
                    $stockMessage = $this->stockHelper->getStockMessage($value, $option->getProductId());
                    $options[$optionId][$valueId]['stockMessage'] = $stockMessage;
                }
            }
        }

        $configObj->setData('config', $options);

        return $configObj;
    }
}
