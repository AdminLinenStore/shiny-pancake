<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Observer;

use Magento\Catalog\Model\Product;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;

class SaveDataFromProductEntity implements ObserverInterface
{
    /**
     * @var \MageWorx\OptionBase\Model\OptionManager
     */
    protected $optionManager;

    /**
     * AddDataToProductEntity constructor.
     * @param \MageWorx\OptionBase\Model\OptionManager $optionManager
     */
    public function __construct(
        \MageWorx\OptionBase\Model\OptionManager $optionManager
    ) {
        $this->optionManager = $optionManager;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getData('product');
        if (!$product || !$product instanceof Product) {
            return $this;
        }

        $this->optionManager->saveDataFromProductAttributes($product);

        return $this;
    }
}
