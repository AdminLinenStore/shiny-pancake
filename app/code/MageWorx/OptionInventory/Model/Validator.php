<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Model;

/**
 * Validator model
 * @package MageWorx\OptionInventory\Model
 */
class Validator extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \MageWorx\OptionInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\OptionInventory\Helper\Stock $stockHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\OptionInventory\Helper\Stock $stockHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->stockHelper = $stockHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Validate Requested with Original data
     *
     * @param array $requestedData Requested Option Values
     * @param array $originData Original Option Values
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($requestedData, $originData)
    {
        foreach ($requestedData as $requestedValue) {
            $originValue = isset($originData[$requestedValue->getId()]) ? $originData[$requestedValue->getId()] : null;
            if (!$this->isAllow($requestedValue, $originValue)) {
                $this->addError($originValue);
            }
        }
    }

    /**
     * Check if allow original qty add requested qty
     *
     * @param \Magento\Framework\DataObject $requestedValue
     * @param \Magento\Catalog\Model\Product\Option\Value $originValue
     * @return bool
     */
    protected function isAllow($requestedValue, $originValue)
    {
        if (!$originValue) {
            return true;
        }

        if (!$originValue->getManageStock()) {
            return true;
        }

        if ($originValue->getQty() <= 0) {
            return false;
        }

        if ($requestedValue->getQty() > $originValue->getQty()) {
            return false;
        }

        return true;
    }

    /**
     * Throw exception
     *
     * @param \Magento\Catalog\Model\Product\Option\Value $value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addError($value)
    {
        $productId = $value->getProductId();
        if ($productId) {
            $formattedQty = $this->stockHelper->floatingQty($value->getQty(), $productId);
        } else {
            $formattedQty = $value->getQty();
        }
        $e = new \Magento\Framework\Exception\LocalizedException(__('We don\'t have as many "'
            . $value->getProductName() . ': ' . $value->getOptionTitle() . ' - ' . $value->getValueTitle()
            . '" as you requested (available qty: ' . $formattedQty . ').'));
        throw $e;
    }
}
