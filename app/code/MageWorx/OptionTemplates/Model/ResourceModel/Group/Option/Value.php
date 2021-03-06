<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\ResourceModel\Group\Option;

/**
 * Group custom option resource model
 *
 */
class Value extends \Magento\Catalog\Model\ResourceModel\Product\Option\Value
{
    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_optiontemplates_group_option_type_value', 'option_type_id');
    }

    /**
     * Get real table name for db table, validated by db adapter
     * Replace product option tables to mageworx group option tables
     *
     * @param string $origTableName
     * @return string
     *
     */
    public function getTable($origTableName)
    {
        switch ($origTableName) {
            case 'catalog_product_option_type_price':
                $tableName = 'mageworx_optiontemplates_group_option_type_price';
                break;
            case 'catalog_product_option_type_title':
                $tableName = 'mageworx_optiontemplates_group_option_type_title';
                break;
            case 'catalog_product_option_type_value':
                $tableName = 'mageworx_optiontemplates_group_option_type_value';
                break;
            default:
                $tableName = $origTableName;
        }

        return parent::getTable($tableName);
    }
}
