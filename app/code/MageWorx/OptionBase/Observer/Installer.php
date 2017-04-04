<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use \MageWorx\OptionBase\Model\Installer as ModelInstaller;

class Installer implements ObserverInterface
{
    protected $installer;

    public function __construct(
        ModelInstaller $installer
    ) {
    
        $this->installer = $installer;
    }

    public function execute(EventObserver $observer)
    {
        $this->installer->install();
    }
}
