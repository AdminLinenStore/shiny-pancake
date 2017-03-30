<?php
namespace Swissup\Reviewreminder\Model\ResourceModel\Entity;
/**
 * Reminders Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Reviewreminder\Model\Entity', 'Swissup\Reviewreminder\Model\ResourceModel\Entity');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';

        $this->addFilterToMap(
            'customer_email',
            'main_table.customer_email'
        );
        $this->addFilterToMap(
            'customer_name',
            new \Zend_Db_Expr('(SELECT CONCAT(a.customer_firstname, \' \', a.customer_lastname))')
        );
        $this->addFilterToMap(
            'products',
            new \Zend_Db_Expr('(SELECT GROUP_CONCAT(\' \', x.name)
                                FROM ' . $this->getTable('sales_order_item') . ' x
                                WHERE a.entity_id = x.order_id
                                    AND x.product_type != \'configurable\')')
        );
    }
    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        return $countSelect;
    }
     /**
     * Join reminder products info
     */
    public function joinOrderInfo()
    {
        $this->join( [ 'a' => $this->getTable('sales_order') ],
            'main_table.order_id = a.entity_id',
            [
                'customer_firstname' => 'customer_firstname',
                'customer_lastname' => 'customer_lastname'
            ]
        )
        ->addExpressionFieldToSelect(
            'customer_name',
            'CONCAT({{customer_firstname}}, \' \', {{customer_lastname}})',
            [
                'customer_firstname' => 'a.customer_firstname',
                'customer_lastname' => 'a.customer_lastname'
            ]
        )
        ->addExpressionFieldToSelect(
            'products',
            '(SELECT GROUP_CONCAT(\' \', x.name)
                FROM ' . $this->getTable('sales_order_item') . ' x
                WHERE {{entity_id}} = x.order_id
                    AND x.product_type != \'configurable\')',
            [ 'entity_id' => 'a.entity_id' ]
        );
        return $this;
    }
}
