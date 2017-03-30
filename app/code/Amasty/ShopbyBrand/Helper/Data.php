<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\ShopbyBrand\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /** @var \Magento\Framework\UrlInterface  */
    protected $_url;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_url = $context->getUrlBuilder();
    }
    
    public function getAllBrandsUrl($scopeCode = null)
    {
        $pageIdentifier = $this->_scopeConfig->getValue('amshopby_brand/general/brands_page', ScopeInterface::SCOPE_STORE, $scopeCode);
        return $this->_url->getUrl($pageIdentifier);
    }
}
