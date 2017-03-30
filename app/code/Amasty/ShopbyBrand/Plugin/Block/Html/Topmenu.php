<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\ShopbyBrand\Plugin\Block\Html;

use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\ScopeInterface;

class Topmenu
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface  */
    protected $_scopeConfig;

    /** @var \Magento\Framework\UrlInterface  */
    protected $_url;

    /** @var \Amasty\ShopbyBrand\Helper\Data  */
    protected $_helper;

    /**
     * Topmenu constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $url
     * @param \Amasty\ShopbyBrand\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $url,
        \Amasty\ShopbyBrand\Helper\Data $helper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        $this->_url = $url;
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return void
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        if (!$this->_isEnabled()) {
            return;
        }
        $node = new Node(
            $this->_getNodeAsArray(),
            'id',
            $subject->getMenu()->getTree(),
            $subject->getMenu()
        );
        $subject->getMenu()->addChild($node);
    }

    /**
     * @return array
     */
    protected function _getNodeAsArray()
    {
        $url = $this->_helper->getAllBrandsUrl();
        return [
            'name' => __('Brands'),
            'id' => 'amasty_shopby_brand_allbrands',
            'url' => $url,
            'has_active' => false,
            'is_active' => $url == $this->_url->getCurrentUrl()
        ];
    }

    /**
     * @return bool
     */
    protected function _isEnabled()
    {
        return (bool) $this->_scopeConfig
            ->getValue('amshopby_brand/general/topmenu_enabled', ScopeInterface::SCOPE_STORE);
    }
}