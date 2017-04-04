<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Plugin\Adminhtml\Product\Edit\Tab\Options;

use \MageWorx\OptionBase\Model\OptionManager;

/**
 * Class AfterGetOptionValues.
 * Add OptionInventory option fields data to Magento option fields data
 *
 * @package MageWorx\OptionBase\Plugin\Adminhtml\Product\Edit\Tab\Options
 */
class AfterGetOptionValues
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
     * Add custom option fields data to Magento option fields data
     *
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option $subject
     * @param array $result
     * @return $result
     */
    public function afterGetOptionValues($subject, $result)
    {
        $options = null;

        if ($subject->getProduct()) {
            $options = $subject->getProduct()->getOptions();
        }

        if ($subject->getGroup()) {
            $options = $subject->getGroup()->getOptions();
        }

        if ($options == null) {
            $options = [];
        }

        $product = $subject->getProduct();

        $this->optionManager
            ->setProduct($product)
            ->setOptions($options)
            ->updateOptionsBeforeDisplay($result);

        $result = $this->optionManager->getOptions();

        return $result;
    }
}
