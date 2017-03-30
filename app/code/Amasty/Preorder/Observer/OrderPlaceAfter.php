<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Observer;


class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    protected $dataHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Preorder\Helper\Data $helper
    )
    {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->dataHelper = $helper;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->dataHelper->preordersEnabled()) {
            return;
        }
        $order = $observer->getEvent()->getOrder();
        $this->dataHelper->checkNewOrder($order);
    }

}
