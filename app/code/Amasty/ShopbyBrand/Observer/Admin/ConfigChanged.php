<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\ShopbyBrand\Observer\Admin;

use Amasty\Shopby\Helper\OptionSetting as SettingHelper;

class ConfigChanged implements \Magento\Framework\Event\ObserverInterface
{
    /** @var  SettingHelper */
    protected $_settingHelper;

    /**
     * Config constructor.
     * @param SettingHelper $settingHelper
     */
    public function __construct(SettingHelper $settingHelper)
    {
        $this->_settingHelper = $settingHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_settingHelper->updateOptionSettings();
    }
}