<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\ShopbyBrand\Observer\Admin;

use Amasty\Shopby\Helper\OptionSetting as SettingHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AttributeSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /** @var  SettingHelper */
    protected $_settingHelper;

    /** @var ScopeConfigInterface */
    protected $_scopeConfig;

    /**
     * AttributeSaveAfter constructor.
     * @param SettingHelper $settingHelper
     * @param ScopeConfigInterface $configInterface
     */
    public function __construct(
        SettingHelper $settingHelper,
        ScopeConfigInterface $configInterface
    ) {
        $this->_settingHelper = $settingHelper;
        $this->_scopeConfig = $configInterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attrCode   = $this->_scopeConfig->getValue('amshopby_brand/general/attribute_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($attrCode == $observer->getEvent()->getAttribute()->getAttributeCode()) {
            $this->_settingHelper->updateOptionSettings();
        }
    }
}
