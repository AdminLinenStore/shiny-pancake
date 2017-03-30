<?php
namespace Swissup\Askit\Model\ResourceModel\Item;

use Swissup\Askit\Api\Data\ItemInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Askit\Model\Item', 'Swissup\Askit\Model\ResourceModel\Item');
    }

    public function addStoreFilter($storeId)
    {
        $this->getSelect()
            ->where('main_table.store_id = ?', $storeId)
        ;
        return $this;
    }

    public function addStatusFilter($status = ItemInterface::STATUS_APPROVED)
    {
        $this->getSelect()
            ->where('main_table.status = ?', $status)
        ;
        return $this;
    }

    public function addPrivateFilter($customerId = null)
    {

        if (null != $customerId) {
            $this->getSelect()->where(
                '(main_table.is_private = 0) OR (main_table.is_private = 1 AND main_table.customer_id = ?)',
                $customerId
            );
        } else {
            $this->getSelect()->where('main_table.is_private = 0');
        }
        return $this;
    }

    public function addProductFilter($id)
    {
        $this->getSelect()
            ->where('main_table.item_type_id = ?', ItemInterface::TYPE_CATALOG_PRODUCT)
            ->where('main_table.item_id = ?', $id)
        ;
        return $this;
    }

    public function addCategoryFilter($id)
    {
        $this->getSelect()
            ->where('main_table.item_type_id = ?', ItemInterface::TYPE_CATALOG_CATEGORY)
            ->where('main_table.item_id = ?', $id)
        ;
        return $this;
    }

    public function addPageFilter($id)
    {
        $this->getSelect()
            ->where('main_table.item_type_id = ?', ItemInterface::TYPE_CMS_PAGE)
            ->where('main_table.item_id = ?', $id)
        ;
        return $this;
    }

    public function addQuestionFilter($questionId)
    {
        $this->getSelect()
            ->where('main_table.parent_id = ?', $questionId)
        ;
        return $this;
    }

    public function addAnswerFilter()
    {
        $this->getSelect()
            ->where('main_table.parent_id IS NOT NULL')
            ->where('main_table.parent_id <> ?', 0)
        ;
        return $this;
    }

    public function addCustomerFilter($customerId)
    {
        $this->getSelect()
            ->where('main_table.customer_id = ?', $customerId)
        ;
        return $this;
    }

    public function addHintOrder($order = 'DESC')
    {
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
        $this->getSelect()
            ->order('main_table.hint ' . $order)
        ;
        return $this;
    }
}
