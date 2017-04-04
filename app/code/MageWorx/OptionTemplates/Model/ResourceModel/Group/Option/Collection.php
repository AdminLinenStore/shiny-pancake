<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model\ResourceModel\Group\Option;

/**
 * Group options collection
 *
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
{
    /**
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \MageWorx\OptionTemplates\Model\ResourceModel\Group\Option\Value\CollectionFactory $valueCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \MageWorx\OptionTemplates\Model\ResourceModel\Group\Option\Value\CollectionFactory $valueCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $valueCollectionFactory,
            $storeManager,
            $connection,
            $resource
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'MageWorx\OptionTemplates\Model\Group\Option',
            'MageWorx\OptionTemplates\Model\ResourceModel\Group\Option'
        );
    }

    /**
     * Retrieve table name
     * Replace product option tables to mageworx group option tables
     *
     * @param string $origTableName
     * @param bool $real
     * @return string
     */
    public function getTable($origTableName, $real = false)
    {
        if ($real) {
            return $origTableName;
        }

        switch ($origTableName) {
            case 'catalog_product_option':
                $tableName = 'mageworx_optiontemplates_group_option';
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

    /**
     * Add group_id filter to select
     *
     * @param array|\MageWorx\OptionTemplates\Model\Group|int $group
     * @return $this
     */
    public function addGroupToFilter($group)
    {
        if (empty($group)) {
            $this->addFieldToFilter('group_id', '');
        } else if (is_array($group)) {
            $this->addFieldToFilter('group_id', ['in' => $group]);
        } else if ($group instanceof \MageWorx\OptionTemplates\Model\Group) {
            $this->addFieldToFilter('group_id', $group->getId());
        } else {
            $this->addFieldToFilter('group_id', $group);
        }

        return $this;
    }

    /**
     * Add product filter
     * @return $this
     * @internal param int $productId
     */
    public function addProductOptionToResultFilter()
    {
        $this->getSelect()
            ->join(
                ['product_option' => $this->getTable('catalog_product_option', true)],
                'product_option.group_option_id = main_table.option_id',
                ['product_options' => 'GROUP_CONCAT(product_option.option_id)']
            )
            ->group('main_table.group_id');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);

        return $this;
    }
}
