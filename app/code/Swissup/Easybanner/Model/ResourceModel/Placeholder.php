<?php
namespace Swissup\Easybanner\Model\ResourceModel;

/**
 * Placeholder mysql resource
 */
class Placeholder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_easybanner_placeholder', 'placeholder_id');
    }

    /**
      * Load an object using 'name' field if there's no field specified and
      * value is not numeric
      *
      * @param \Magento\Framework\Model\AbstractModel $object
      * @param mixed $value
      * @param string $field
      * @return $this
      */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'name';
        }
        return parent::load($object, $value, $field);
    }
}
