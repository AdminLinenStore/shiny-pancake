<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin;

class LayeredNavigationPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    public function afterToHtml(\Magento\Framework\View\Element\Template $subject, $result)
    {
        $enableOverflowScroll = $this->_scopeConfig->getValue('amshopby/general/enable_overflow_scroll', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($enableOverflowScroll) {
            $result = str_replace('block filter', 'block filter amshopby-overflow-scroll-enabled', $result);
            if (strpos($result, 'block filter') !== false) {
                $result = $result
                    . '<style>'
                    . 'div.amshopby-overflow-scroll-enabled div.block-content .filter-options .filter-options-content ol:first-of-type { max-height: ' . $enableOverflowScroll . 'px; overflow-y: auto; overflow-x: -moz-hidden-unscrollable;}'
                    . '</style>';
            }
        }
        return $result;
    }
}
