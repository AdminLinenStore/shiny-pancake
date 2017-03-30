<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */


/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Block\Product\View\Preorder;


use Magento\Framework\View\Element\Template;

class ProductDefault extends ProductAbstract
{
    /**
     * @return bool
     */
    public function canShowBlock()
    {
        return parent::canShowBlock() && $this->helper->getIsProductPreorder($this->getProduct());
    }
}
