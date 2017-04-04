<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\ResourceModel\Group;

class Option extends \Magento\Catalog\Model\ResourceModel\Product\Option
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Currency factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Core config model
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_optiontemplates_group_option', 'option_id');
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
            case 'catalog_product_option':
                $tableName = 'mageworx_optiontemplates_group_option';
                break;
            case 'catalog_product_option_title':
                $tableName = 'mageworx_optiontemplates_group_option_title';
                break;
            case 'catalog_product_option_price':
                $tableName = 'mageworx_optiontemplates_group_option_price';
                break;
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
