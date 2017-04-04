<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Plugin\Adminhtml\Product;

use \MageWorx\OptionBase\Model\OptionManager;

/**
 * Class AroundGetOptionsAsArray
 *
 * @package MageWorx\OptionBase\Plugin\Adminhtml\Product
 */
class AroundGetOptionsAsArray
{
    /**
     * @var Option
     */
    protected $optionManager;

    /**
     * AroundGetOptionsAsArray constructor.
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
    /**
     * Processes options data*
     *
     * @param \MageWorx\OptionTemplates\Model\OptionConverter $subject
     * @param \Closure $proceed
     * @param null|\MageWorx\OptionTemplates\Model\Group $group
     * @return array
     */
    public function aroundGetOptionsAsArray($subject, \Closure $proceed, $group = null)
    {
        // Do original converter options
        $result = $proceed($group);

        // After original Do update custom options
        if ($group instanceof \MageWorx\OptionTemplates\Model\Group) {
            $options = $group->getOptions();

            if ($options == null) {
                $options = [];
            }

            $this->optionManager
                ->setProduct(null)
                ->setOptions($options)
                ->updateOptionsBeforeGroupSave($result);

            $result = $this->optionManager->getOptions();
        }

        return $result;
    }
}
