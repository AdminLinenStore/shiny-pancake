<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Class JoinProductCollection
 * @package MageWorx\OptionBase\Observer
 * @deprecated
 */
class JoinProductCollection implements ObserverInterface
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
        $collection = $observer->getData('collection');
        if (!$collection || !$collection instanceof ProductCollection) {
            return $this;
        }

        $this->optionManager->joinProductCollection($collection);

        return $this;
    }
}
