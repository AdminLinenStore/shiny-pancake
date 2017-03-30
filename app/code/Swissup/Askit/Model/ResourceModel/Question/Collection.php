<?php
namespace Swissup\Askit\Model\ResourceModel\Question;

use Swissup\Askit\Model\ResourceModel\Item\Collection as ItemCollection;
use Swissup\Askit\Model\Item;

class Collection extends ItemCollection
{
    /**
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->addQuestionFilter(0);
        // $select = $this->getSelect();
        // $select->where('main_table.parent_id = ?', 0);

        return parent::_beforeLoad();
    }


   /**
    * Initialize system messages after load
    *
    * @return $this
    */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->addCountAnswers()
            // ->addItemInfo()
            ;
        return $this;
    }

    /**
     *
     * @return $this
     */
    public function addCountAnswers()
    {
        $select = $this->_conn->select()
            ->from(
                ['main_table' => $this->getMainTable()],
                array('parent_id', 'count_answers' => 'COUNT(id)')
            )
            ->where('parent_id <> ?', 0)
            ->group('parent_id')
        ;
        $counts = array();
        foreach ($this->_conn->fetchAll($select) as $row) {
            $counts[$row['parent_id']] = $row['count_answers'];
        }

        foreach ($this->_items as $key => $item) {
            $count = 0;
            if (isset($counts[$item->getId()])) {
                $count = $counts[$item->getId()];
            }
            $item->setData('count_answers', $count);
        }
        return $this;
    }

    // /**
    //  *
    //  * @return $this
    //  */
    // public function addItemInfo()
    // {
    //     foreach ($this->_items as $key => $item) {

    //         // $count = 0;
    //         // if (isset($counts[$item->getId()])) {
    //         //     $count = $counts[$item->getId()];
    //         // }
    //         // $item->setData('count_answers', $count);
    //     }
    //     return $this;
    // }
    //
}
