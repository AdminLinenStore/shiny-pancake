<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * StockProvider model.
 * @package MageWorx\OptionInventory\Model
 */
class StockProvider extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * OptionInventory Stock helper
     *
     * @var \MageWorx\OptionInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * StockProvider constructor.
     *
     * @param ObjectManager $objectManager
     * @param \MageWorx\OptionInventory\Helper\Stock $stockHelper
     */
    public function __construct(
        ObjectManager $objectManager,
        \MageWorx\OptionInventory\Helper\Stock $stockHelper
    ) {

        $this->objectManager = $objectManager;
        $this->stockHelper = $stockHelper;
    }

    /**
     * Retrieve Original option values data
     *
     * @param array $requestedData Options array
     * @return array
     */
    public function getOriginData($requestedData)
    {
        $originalData = [];

        $valuesId = array_keys($requestedData);
        $valuesCollection = $this->objectManager
            ->create('\MageWorx\OptionInventory\Model\ResourceModel\Product\Option\Value\Collection')
            ->getValuesByOption($valuesId);

        foreach ($valuesCollection as $value) {
            $originalData[$value->getId()] = $value;
        }

        return $originalData;
    }

    /**
     * Retrieve Requested option values data
     *
     * @param array $items Quote items array
     * @param array $cart Option array retrieved from POST
     * @return array
     */
    public function getRequestedData($items, $cart)
    {
        $requestedData = [];

        $items = !is_array($items) ? [$items] : $items;

        foreach ($items as $item) {
            $itemRequestedData = $this->getItemData($item, $cart);

            foreach ($itemRequestedData as $valueId => $valueData) {
                if (isset($requestedData[$valueId])) {
                    $value = $requestedData[$valueId];
                    $value->setQty($value->getQty() + $valueData->getQty());
                } else {
                    $requestedData[$valueId] = $valueData;
                }
            }
        }

        return $requestedData;
    }

    /**
     * Retrieve item option values data
     *
     * @param \Magento\Quote\Model\Quote\Item $item Quote item
     * @param array $cart Option array retrieved from POST
     * @return array
     */
    public function getItemData($item, $cart = [])
    {
        $requestedData = [];

        $itemInfo = $this->getItemInfo($item);
        $itemOptions = isset($itemInfo['options']) ? $itemInfo['options'] : [];
        $itemQty = $item->getQty() ? $item->getQty() : 1;
        $itemQty = isset($cart[$item->getId()]) ? $cart[$item->getId()]['qty'] : $itemQty;

        $valuesId = $this->stockHelper->getRequestedValuesId($itemOptions);

        foreach ($valuesId as $key => $valueId) {
            $value = new \Magento\Framework\DataObject();
            $value->setId($valueId);
            $value->setQty($itemQty);

            $requestedData[$valueId] = $value;
        }

        return $requestedData;
    }

    /**
     * Retrieve item info
     *
     * @param \Magento\Quote\Model\Quote\Item $item Quote Item
     * @return mixed
     */
    protected function getItemInfo($item)
    {
        $itemOptions = $item->getOptionsByCode();
        $itemInfoBuyRequest = $itemOptions['info_buyRequest'];
        $itemDataSerialized = $itemInfoBuyRequest->getData('value');
        $itemData = unserialize($itemDataSerialized);

        return $itemData;
    }
}
