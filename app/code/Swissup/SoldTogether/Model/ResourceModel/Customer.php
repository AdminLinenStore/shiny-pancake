<?php
namespace Swissup\SoldTogether\Model\ResourceModel;

/**
 * SoldTogether Customer mysql resource
 */
class Customer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_soldtogether_customer', 'relation_id');
    }

    public function getCustomerOrderIds($count, $step)
    {
        $connection = $this->getConnection();

        $customerSelect = $connection->select()
            ->from($this->getTable('customer_entity'), 'email')
            ->order('entity_id')
            ->limit($count, $count * $step);

        $customerMail = $connection->fetchCol($customerSelect);

        $customerMail = array_filter($customerMail);

        $select = $connection->select()
            ->from(array('soi' => $this->getTable('sales_order_item')),
                array('product_id', 'parent_item_id', 'name'))
            ->join(array('so' => $this->getTable('sales_order')),
                'so.entity_id = soi.order_id',
                array('customer_email', 'store_id'))
            ->joinInner(array('cp' => $this->getTable('catalog_product_entity')),
                'cp.entity_id = soi.product_id',
                array())
            ->where('so.customer_email IN (?)', $customerMail)
            ->order(array('order_id', 'product_id'));
        $result = [];

        foreach ($connection->fetchAll($select) as $row) {
            if (!$row['parent_item_id']) {
                $result[$row['product_id']] = ['name' => $row['name'], 'store' => $row['store_id']];
            }
        }

        return $result;
    }

    public function getRelatedProductData($productId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['related_id', 'weight']
        )
        ->where('product_id = ?', $productId);

        return $connection->fetchAll($select);
    }

    public function getCustomerNewOrderIds($customerEmail, $orderId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(array('soi' => $this->getTable('sales_order_item')),
                array('product_id', 'parent_item_id', 'name'))
            ->join(array('so' => $this->getTable('sales_order')),
                'so.entity_id = soi.order_id',
                array('customer_email', 'store_id'))
            ->joinInner(array('cp' => $this->getTable('catalog_product_entity')),
                'cp.entity_id = soi.product_id',
                array())
            ->where('so.entity_id <> ?', $orderId)
            ->where('so.customer_email = ?', $customerEmail)
            ->where('so.store_id = ?', $storeId)
            ->order(array('order_id', 'product_id'));
        $result = [];

        foreach ($connection->fetchAll($select) as $row) {
            if (!$row['parent_item_id']) {
                $result[$row['product_id']] = ['name' => $row['name'], 'store' => $row['store_id']];
            }
        }

        return $result;
    }

    public function relationExist($productId, $relatedId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            'relation_id'
        )->where('product_id = ?', $productId
        )->where('related_id = ?', $relatedId);

        $result = $connection->fetchCol($select);

        if (count($result) > 0) {
            return $result[0];
        }

        return false;
    }

    public function deleteAllCustomerRelations()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['is_admin=?' => 0]
        );
    }

    public function deleteProductCustomerRelations($productId)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['product_id=?' => $productId]
        );
    }
}
