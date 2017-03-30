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


class ListProduct
{
    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    protected $helper;

    public function __construct(\Amasty\Preorder\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function aroundGetReviewsSummaryHtml(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        \Closure $closure,
        \Magento\Catalog\Model\Product $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        $htmlPreorder = '';
        if($this->helper->preordersEnabled() && $this->helper->getIsProductPreorder($product)) {
            $htmlPreorder = $subject->getLayout()->createBlock('Amasty\Preorder\Block\Product\ListProduct\Preorder')->setProduct($product)->setTemplate('product/list/preorder.phtml')->toHtml();
        }

        return $htmlPreorder.$closure($product, $templateType, $displayIfNoReviews);
    }
}
