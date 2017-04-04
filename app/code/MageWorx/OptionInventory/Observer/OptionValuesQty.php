<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageWorx\OptionInventory\Observer;

use \MageWorx\OptionInventory\Model\StockProvider;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as OptionValueCollection;

/**
 * Prepare array with information about used option values qty
 */
class OptionValuesQty
{
    /**
     * @var StockProvider
     */
    protected $stockProvider;

    /**
     * @var OptionValueCollection
     */
    protected $valueCollection;

    /**
     * OptionValuesQty constructor.
     * @param OptionValueCollection $valueCollection
     * @param ValueModel $valueModel
     */
    public function __construct(
        StockProvider $stockProvider,
        OptionValueCollection $valueCollection
    ) {
    
        $this->stockProvider = $stockProvider;
        $this->valueCollection = $valueCollection;
    }

    /**
     * Retrive array of [valueId => qty] to substruct this
     *
     * @param array $requestedItems Requested Option Values
     * @return array
     */
    public function getItemsToCorrect($requestedItems)
    {
        $itemsToCorrect = [];

        $requestedValues = $this->stockProvider->getRequestedData($requestedItems, []);

        foreach ($requestedValues as $value) {
            $this->_addItemToArray($itemsToCorrect, $value);
        }

        return $itemsToCorrect;
    }

    /**
     * Adds option value qty to $itemsToCorrect (creates new entry or increments existing one)
     *
     * @param array $itemsToCorrect
     * @param \Magento\Framework\DataObject $value
     * @return void
     */
    protected function _addItemToArray(&$itemsToCorrect, $value)
    {
        $valueId = $value->getId();
        $valueQty = $value->getQty();

        if (isset($itemsToCorrect[$valueId])) {
            $itemsToCorrect[$valueId] += $valueQty;
        } else {
            $itemsToCorrect[$valueId] = $valueQty;
        }
    }
}
