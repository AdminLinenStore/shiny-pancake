<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Shopby\Setup;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Amasty\Shopby\Helper\OptionSetting as SettingHelper;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Amasty\Base\Helper\Deploy
     */
    protected $deployHelper;

    /** @var  SettingHelper */
    protected $_settingHelper;

    /**
     * UpgradeData constructor.
     * @param \Amasty\Base\Helper\Deploy $deployHelper
     * @param SettingHelper $settingHelper
     */
    public function __construct(
        \Amasty\Base\Helper\Deploy $deployHelper,
        SettingHelper $settingHelper
    ) {
        $this->deployHelper = $deployHelper;
        $this->_settingHelper = $settingHelper;
    }
    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '1.6.3', '<')) {
            $this->deployPub();
        }

        if (version_compare($context->getVersion(), '1.9.0', '<')) {
            $this->_settingHelper->updateOptionSettings();
        }
    }

    protected function deployPub()
    {
        $p = strrpos(__DIR__, DIRECTORY_SEPARATOR);
        $modulePath = $p ? substr(__DIR__, 0, $p) : __DIR__;
        $modulePath .= '/pub';
        $this->deployHelper->deployFolder($modulePath);
    }
}
