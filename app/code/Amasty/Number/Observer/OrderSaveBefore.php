<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Number\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderSaveBefore implements ObserverInterface
{
    protected $_salesObjects = array(
        'invoice', 'shipment', 'creditmemo'
    );

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $type = '';
        $doc = null;

        foreach ($this->_salesObjects as $t){
            if (is_object($observer->getData($t))){
                $type = $t;
            }
        }

        if (!$type){
            return;
        }

        if (in_array($type, $this->_salesObjects))
            $doc = $observer->getData($type);

        if ($doc->getId()) { // do not need change the `Increment Id` if a document is not new
            return;
        }

        $order   = $doc->getOrder();
        $storeId = $order->getStore()->getStoreId();

        if (!$this->scopeConfig->isSetFlag(
            'amnumber/' . $type . '/prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )){
            return;
        }

        if ( !$this->scopeConfig->isSetFlag(
            'amnumber/' . $type . '/prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        )){
            return;
        }

        $number  = 0;
        $counter = 0;

        while (!$number) {
            $number  = $order->getIncrementId();
            $prefix = $this->scopeConfig->getValue(
                'amnumber/' . $type . '/prefix',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $replace = $this->scopeConfig->getValue(
                'amnumber/' . $type . '/replace',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if ($replace){
                $number = str_replace($replace, $prefix, $number);
            }
            else {
                $number = $prefix . $number;
            }


            if ($counter) {
                $number .= '-' . $counter;
            }

            $collection = $this->_getCollection($type)
                ->addFieldToFilter('increment_id', $number)
                ->setPageSize(1);

            if (count($collection)){
                $number = 0;
            }

            ++$counter;
        }

        $doc->setIncrementId($number);
    }

    protected function _getCollection($type){
        $ret = null;
        if (in_array($type, $this->_salesObjects)){
            $ret = $this->objectManager->create('Magento\Sales\Model\Order\\'.ucfirst($type))->getCollection();
        }
        return $ret;
    }
}