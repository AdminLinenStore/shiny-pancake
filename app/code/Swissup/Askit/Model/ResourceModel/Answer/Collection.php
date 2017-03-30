<?php namespace Swissup\Askit\Model\ResourceModel\Answer;

class Collection extends \Swissup\Askit\Model\ResourceModel\Item\Collection
{
    /**
     * Add filter by only ready fot sending item
     *
     * @return $this
     */
    public function addParentIdFilter($parentId)
    {
        $this->getSelect()->where('main_table.parent_id = ?', $parentId);
        return $this;
    }
}