<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Plugin;


class SalesOrderItem
{
    protected $helper;

    public function __construct(\Amasty\Preorder\Helper\Data $helper)
    {
        $this->helper = $helper;
    }


    public function afterGetName(\Magento\Sales\Model\Order\Item $subject, $result)
    {
        $preorderFlag = $this->helper->getOrderItemIsPreorderFlag($subject->getId());
        if($preorderFlag) {
            $result .=  ' ' . __('(Preorder)');
        }
        return $result;
    }

}
