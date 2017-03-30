<?php
namespace Swissup\Askit\Model\Item;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     *
     * @return \Swissup\Askit\Model\Item
     */
    public function create()
    {
        return $this->_objectManager->create('Swissup\Askit\Model\Item');
    }
}
