<?php
namespace Swissup\SoldTogether\Model\ResourceModel;

/**
 * SoldTogether Order mysql resource
 */
class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_soldtogether_order', 'relation_id');
    }

    public function getOrderIdsToReindex($count, $step)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('sales_order'),
            'entity_id'
        )->order('entity_id')
        ->limit($count, $count * $step);

        return $connection->fetchCol($select);
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

    public function relationExist($productId, $relatedId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            'relation_id'
        )->where('product_id = ?', $productId
        )->where('related_id = ?', $relatedId
        )->where('store_id = ?', $storeId);

        $result = $connection->fetchCol($select);
        if (count($result) > 0) {
            return $result[0];
        }

        return false;
    }

    public function deleteAllOrderRelations()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['is_admin=?' => 0]
        );
    }

    public function deleteProductOrderRelations($productId)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['product_id=?' => $productId]
        );
    }
}
