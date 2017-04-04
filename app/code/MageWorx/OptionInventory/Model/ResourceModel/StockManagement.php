<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Model\ResourceModel;

/**
 * StockManagement Resource model.
 * @package MageWorx\OptionInventory\Model\ResourceModel
 */
class StockManagement extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_option_type_value', 'option_type_id');
    }

    /**
     * StockManagement constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Correct particular stock option value qty based on operator
     *
     * @param int[] $items
     * @param string $operator +/-
     * @return void|$this
     */
    public function correctItemsQty(array $items, $operator)
    {
        if (empty($items)) {
            return $this;
        }

        $connection = $this->getConnection();
        $conditions = [];
        foreach ($items as $optionTypeId => $qty) {
            $case = $connection->quoteInto('?', $optionTypeId);
            $result = $connection->quoteInto("qty{$operator}?", $qty);
            $conditions[$case] = $result;
        }

        $value = $connection->getCaseSql('option_type_id', $conditions, 'qty');
        $where = ['option_type_id IN (?)' => array_keys($items)];

        $connection->beginTransaction();
        $connection->update($this->getTable('catalog_product_option_type_value'), ['qty' => $value], $where);
        $connection->commit();
    }
}
