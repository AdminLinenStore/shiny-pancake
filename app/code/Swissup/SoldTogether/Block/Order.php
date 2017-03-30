<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\SoldTogether\Block;

use Magento\Customer\Model\Context;

class Order extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var Collection
     */
    protected $_orderCollection;

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData(
            [
                'cache_lifetime' => 86400,
                'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG]
            ]
        );
    }
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configHelper = $_objectManager->get('Swissup\SoldTogether\Helper\Config');
        $httpContext = $_objectManager->get('Magento\Framework\App\Http\Context');
        $product = $this->_coreRegistry->registry('product');

        return [
            'SOLDTOGETHER_ORDER',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $httpContext->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $configHelper->getOrderLimit(),
            $product->getId()
        ];
    }

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Product\Visibility $catalogVisibility,
        array $data = []
    ) {
        $this->_catalogProductVisibility = $catalogVisibility;
        $this->moduleManager = $moduleManager;
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _prepareSoldTogetherOrderData()
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configHelper = $_objectManager->get('Swissup\SoldTogether\Helper\Config');
        $stockHelper = $_objectManager->get('Magento\CatalogInventory\Helper\Stock');

        if (!$configHelper->canShowOrderBlock()) {
            return $this;
        }
        $product = $this->_coreRegistry->registry('product');
        /* @var $product \Magento\Catalog\Model\Product */
        $productCollection = $_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $resource = $_objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_orderCollection = $productCollection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_orderCollection);
        }
        $this->_orderCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_orderCollection->getSelect()
            ->joinInner(
                array('sc' => $resource->getTableName('swissup_soldtogether_order')),
                'sc.related_id=e.entity_id and sc.product_id=' . $product->getId(),
                array('soldtogether_weight' => 'sc.weight')
            );

        $this->_orderCollection->getSelect()->order('soldtogether_weight ' . \Magento\Framework\DB\Select::SQL_DESC);
        $this->_orderCollection->getSelect()
            ->where('e.type_id IN (?)', array('simple', 'virtual'));

        $this->_orderCollection->getSelect()->limit($configHelper->getOrderLimit());

        if ($configHelper->canShowOrderOutOfStock()) {
            $stockHelper->addInStockFilterToCollection($this->_orderCollection);
        }

        if ($this->_orderCollection->count() === 0 && $configHelper->canShowOrderRandom()) {
            $this->getRandomCollection($product);
        }

        return $this;
    }

    protected function _beforeToHtml()
    {
        $this->_prepareSoldTogetherOrderData();
        return parent::_beforeToHtml();
    }

    public function getRandomCollection($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configHelper = $objectManager->get('Swissup\SoldTogether\Helper\Config');
        $stockHelper = $objectManager->get('Magento\CatalogInventory\Helper\Stock');
        $category = $objectManager->get('Magento\Catalog\Model\Category');
        $category->load($product->getCategoryId());
        $this->_orderCollection = $category->getProductCollection();

        $this->_orderCollection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        $this->_addProductAttributesAndPrices($this->_orderCollection);
        $this->_orderCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_orderCollection->addAttributeToFilter('entity_id', array('nin' => array($product->getId())));
        $this->_orderCollection->getSelect()
            ->where('e.type_id IN (?)', array('simple', 'virtual'));

        if ($configHelper->canShowOrderOutOfStock()) {
            $stockHelper->addInStockFilterToCollection($this->_orderCollection);
        }

        $this->_orderCollection->getSelect()->order('rand()');
        $this->_orderCollection->getSelect()->limit($configHelper->getOrderLimit());

        return $this;
    }

    public function getPriceFormat()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $localeFormat = $objectManager->get('Magento\Framework\Locale\FormatInterface');

        return $localeFormat->getPriceFormat();
    }

    public function getItems()
    {
        return $this->_orderCollection;
    }

    public function getTaxDisplayConfig()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

        return $scopeConfig->getValue("tax/display/type");
    }

    public function getIdentities()
    {
        $identities = [];
        if ($this->getItems()) {
            foreach ($this->getItems() as $item) {
                $identities = array_merge($identities, $item->getIdentities());
            }
        }

        return $identities;
    }
}
