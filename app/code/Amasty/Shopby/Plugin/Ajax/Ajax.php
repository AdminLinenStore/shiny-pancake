<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Shopby\Plugin\Ajax;

use Magento\Framework\App\Action\Action;

class Ajax
{
    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /** @var \Amasty\Shopby\Helper\UrlBuilder  */
    protected $urlBuilder;

    /**
     * CategoryViewAjax constructor.
     *
     * @param \Amasty\Shopby\Helper\Data                      $helper
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Amasty\Shopby\Helper\Data $helper,
        \Amasty\Shopby\Helper\UrlBuilder $urlBuilder,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->helper = $helper;
        $this->resultRawFactory = $resultRawFactory;
        $this->urlBuilder = $urlBuilder;
    }

    protected function isAjax(Action $controller)
    {
        $isAjax = $controller->getRequest()->isAjax();
        $isScroll = $controller->getRequest()->getParam('is_scroll');
        return $this->helper->isAjaxEnabled() && $isAjax && !$isScroll;
    }

    /**
     * @param \Magento\Framework\View\Result\Page $page
     *
     * @return array
     */
    protected function getAjaxResponseData(\Magento\Framework\View\Result\Page $page)
    {
        $layout = $page->getLayout();

        $products = $layout->getBlock('category.products');
        if (!$products) {
            $products = $layout->getBlock('search.result');
        }
        $navigation = $layout->getBlock('catalog.leftnav');
        if (!$navigation) {
            $navigation = $layout->getBlock('catalogsearch.leftnav');
        }
        $applyButton = $layout->getBlock('amasty.shopby.applybutton.sidebar');

        $navigationTop = $layout->getBlock('amshopby.catalog.topnav');
        $applyButtonTop = $layout->getBlock('amasty.shopby.applybutton.topnav');
        $h1 = $layout->getBlock('page.main.title');
        $title = $page->getConfig()->getTitle();
        $breadcrumbs = $layout->getBlock('breadcrumbs');

        $htmlCategoryData = '';
        $children = $layout->getChildNames('category.view.container');
        foreach ($children as $child) {
            $htmlCategoryData .= $layout->renderElement($child);
        }
        $htmlCategoryData = '<div class="category-view">' . $htmlCategoryData . '</div>';

        $shopbyCollapse = $layout->getBlock('catalog.navigation.collapsing');
        $shopbyCollapseHtml = '';
        if($shopbyCollapse) {
            $shopbyCollapseHtml = $shopbyCollapse->toHtml();
        }
        $navigation->toHtml();

        $responseData = [
            'categoryProducts'=> $products ? $products->toHtml() : '',
            'navigation' => ($navigation ? $navigation->toHtml() : '') . $shopbyCollapseHtml . ($applyButton ? $applyButton->toHtml() : ''),
            'navigationTop' => ($navigationTop ? $navigationTop->toHtml() : '') . ($applyButtonTop ? $applyButtonTop->toHtml() : ''),
            'breadcrumbs' => $breadcrumbs ? $breadcrumbs->toHtml() : '',
            'h1' => $h1 ? $h1->toHtml() : '',
            'title' => $title->get(),
            'categoryData' => $htmlCategoryData,
            'url' => $this->urlBuilder->getUrl('*/*/*', [
                '_current' => true,
                '_use_rewrite' => true
            ])
        ];

        return $responseData;
    }

    /**
     * @param array $data
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    protected function prepareResponse(array $data)
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($data));
        return $response;
    }
}
