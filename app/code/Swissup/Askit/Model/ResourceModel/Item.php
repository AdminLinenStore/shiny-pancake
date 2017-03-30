<?php
namespace Swissup\Askit\Model\ResourceModel;

/**
 * Askit Item mysql resource
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Core Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @par am \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        $resourcePrefix = null
    ) {
        $this->_coreDate = $coreDate;
        parent::__construct($context, $resourcePrefix);
        // $this->_date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_askit_item', 'id');
    }

    /**
     * Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);

        if ($object->isObjectNew() && !$object->hasCreatedTime()) {
            $object->setCreatedTime($this->_coreDate->gmtDate());
        }

        if ($object->isObjectNew() && !$object->hasParentId()) {
            $object->setParentId(0);
        }

        $object->setUpdateTime($this->_coreDate->gmtDate());

        return $this;
    }
}
