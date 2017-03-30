<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */

namespace Swissup\SoldTogether\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    public function canShowOrderBlock()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/order/enabled",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function canShowCustomerBlock()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/customer/enabled",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    public function canShowOrderRandom()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/order/random",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function canShowCustomerRandom()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/customer/random",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function canShowOrderOutOfStock()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/order/out",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function canShowCustomerOutOfStock()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/customer/out",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function canShowProductWithOptions()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/customer/options",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getOrderLimit()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/order/count",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCustomerLimit()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/customer/count",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getOrderEmailLimit()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/email/order_count",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCustomerEmailLimit()
    {
        return (int)$this->_scopeConfig->getValue(
            "soldtogether/email/customer_count",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
