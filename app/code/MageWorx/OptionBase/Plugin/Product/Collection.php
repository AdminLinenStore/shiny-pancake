<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Plugin\Product;

class Collection
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
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad($subject, $printQuery = false, $logQuery = false)
    {
        $this->optionManager->joinProductCollection($subject);

        return [$printQuery, $logQuery];
    }
}
