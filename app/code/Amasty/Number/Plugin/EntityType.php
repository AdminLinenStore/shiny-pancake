<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Number\Plugin;

class EntityType
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->objectManager = $objectManager;
        $this->_storeManager = $storeManager;
    }

    /**
     * Retreive new incrementId
     *
     * @param int $storeId
     * @return string
     */
    public function aroundReserveOrderId(
        \Magento\Quote\Model\Quote $quote,
        \Closure $closure
    )
    {
        $storeId = $quote->getStoreId();
        $incrementId = $quote->getReservedOrderId();
        if (!$this->_getStoreConfig('amnumber/general/enabled', $storeId) || $incrementId){
            return $closure();
        }

        $type = 'order';

        // same mumber as order
        if ($this->_getStoreConfig('amnumber/'. $type .'/same', $storeId)){
            return $closure();
        }

        $timeOffset = trim($this->_getStoreConfig('amnumber/general/offset', $storeId));
        if (!preg_match('/^[+\-]\d+$/', $timeOffset)){
            $timeOffset = 0;
        }
        $now = 3600*$timeOffset + time();

        $cfg = $this->_getStoreConfig('amnumber/' . $type, $storeId);

        //get last counter value and update it
        $start = max(intVal($cfg['start']), 0);

        $oldDate = $this->_getNotCachedConfig('date', $storeId);

        $last = $this->_getNotCachedConfig('counter', $storeId);
        if ($last->getValue() > 0 ){ // not first time
            if ($cfg['reset']){ //we track date change
                // date has changed
                if (!$oldDate->getValue() || date($cfg['reset'], $now) != date($cfg['reset'], strtotime($oldDate->getValue()))){
                    $last->setValue($start);
                }
            }
        }

        $oldDate->setValue(date('Y-m-d', $now));
        $oldDate->save();

        $counter = max(intVal($last->getValue()), $start) + max(intVal($cfg['increment']), 1);

        $last->setValue($counter);
        $last->save();

        if (intVal($cfg['pad'])){
            $counter = str_pad($counter, intVal($cfg['pad']), '0', STR_PAD_LEFT);
        }
        $vars = array(
            'store_id' => $storeId,
            'store'    => $storeId,
            'yy'       => date('y', $now),
            'yyyy'     => date('Y', $now),
            'mm'       => date('m', $now),
            'm'        => date('n', $now),
            'dd'       => date('d', $now),
            'd'        => date('j', $now),
            'hh'       => date('H', $now),
            'rand'     => rand(1000,9999),
            'counter'  => $counter,
        );

        $incrementId = $cfg['format'];

        foreach ($vars as $k => $v){
            $incrementId = str_replace('{'. $k .'}', $v, $incrementId);
        }


         $quote->setReservedOrderId($incrementId);

        return $closure();
    }

    protected function _getStoreConfig($path, $storeId)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Gets not cached config row as object.
     *
     * @param string $path
     * @param int $storeId
     * @return \Magento\Framework\App\Config\Value
     */
    protected function _getNotCachedConfig( $path, $storeId)
    {
        $type = 'order';
        $cfg = $this->_getStoreConfig('amnumber/' . $type, $storeId);

        $scope   = 'default';
        $scopeId = 0;
        if ($cfg['per_store']){
            $scope   = 'stores';
            $scopeId = $storeId;
        }
        elseif ($cfg['per_website']){
            $scope   = 'websites';
            $scopeId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        }
        //'core/config_data_collection'
        $collection = $this->objectManager->create("Magento\Config\Model\ResourceModel\Config\Data\Collection");
        $collection->addFieldToFilter('scope', $scope);
        $collection->addFieldToFilter('scope_id', $scopeId);
        $collection->addFieldToFilter('path', 'amnumber/' . $type . '/' . $path);
        $collection->setPageSize(1);

        $v = $this->objectManager->create('Magento\Framework\App\Config\Value');
        if (count($collection)){
            $v = $collection->getFirstItem();
        }
        else {
            $v->setScope($scope);
            $v->setScopeId($scopeId);
            $v->setPath('amnumber/' . $type . '/' . $path);
        }

        return $v;
    }
}
