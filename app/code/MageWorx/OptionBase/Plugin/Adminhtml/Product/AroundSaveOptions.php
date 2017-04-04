<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Plugin\Adminhtml\Product;

use \MageWorx\OptionBase\Model\OptionManager;

/**
 * Class AroundSaveOptions.
 * Processes data of OptionInventory options.
 *
 * @package MageWorx\OptionBase\Plugin\Adminhtml\Product
 */
class AroundSaveOptions
{
    /**
     * @var Option
     */
    protected $optionManager;

    /**
     * AroundSaveOptions constructor.
     *
     * @param Option $optionManager
     */
    public function __construct(
        OptionManager $optionManager
    ) {
    
        $this->optionManager = $optionManager;
    }

    /**
     * Processes options data
     *
     * @param \Magento\Catalog\Model\Product\Option $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundSaveOptions(\Magento\Catalog\Model\Product\Option $subject, \Closure $proceed)
    {
        $options = $subject->getOptions();
        if ($options == null) {
            $options = [];
        }

        $product = $subject->getProduct();

        $this->optionManager
            ->setProduct($product)
            ->setOptions($options)
            ->updateOptionsBeforeProductSave();

        $options = $this->optionManager->getOptions();

        $subject->setOptions($options);

        return $proceed();
    }
}
