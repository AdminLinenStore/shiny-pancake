<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Model\ResourceModel\Product\Option\Value;

/**
 * Catalog product option values collection
 * @package MageWorx\OptionInventory\Model\ResourceModel\Product\Option\Value
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Add option filter
     *
     * @param array $optionIds
     * @param int $storeId
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValuesByOption($optionIds, $storeId = null)
    {
        if (!is_array($optionIds)) {
            $optionIds = [$optionIds];
        }

        $this->addFieldToFilter('main_table.option_type_id', ['in' => $optionIds]);

        $this->getSelect()->joinLeft(
            ['cpott' => $this->getTable('catalog_product_option_type_title')],
            'cpott.option_type_id = main_table.option_type_id',
            ['value_title' => 'title']
        )->joinLeft(
            ['cpot' => $this->getTable('catalog_product_option_title')],
            'cpot.option_id = main_table.option_id',
            ['option_title' => 'title']
        )->joinLeft(
            array('cpo' => $this->getTable('catalog_product_option')),
            'cpo.option_id = main_table.option_id',
            array('cpo.product_id')
        )->joinLeft(
            array('pn' => new \Zend_Db_Expr($this->getTableProductName())),
            'pn.entity_id = cpo.product_id',
            array('product_name'=>'pn.product_name')
        );

        return $this;
    }

    /**
     * Retrieve table with product name
     *
     * @return string
     */
    private function getTableProductName()
    {
        $tableCPEV = $this->getTable('catalog_product_entity_varchar');
        $tableEET = $this->getTable('eav_entity_type');
        $tableEA = $this->getTable('eav_attribute');
        return '(SELECT cpev.entity_id as entity_id, cpev.attribute_id as attribute_id, cpev.value as product_name
                FROM '.$tableCPEV.' as cpev
                WHERE attribute_id = (
                   SELECT e.attribute_id
                   FROM '.$tableEA.' e
                   LEFT JOIN '.$tableEET.' AS t ON e.entity_type_id = t.entity_type_id
                   WHERE e.attribute_code = \'name\' AND t.entity_type_code = \'catalog_product\'
                ) AND cpev.store_id = 0)';
    }
}
