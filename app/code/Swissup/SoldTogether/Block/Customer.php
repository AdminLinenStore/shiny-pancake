<?php
namespace Swissup\SoldTogether\Block;

use Magento\Customer\Model\Context;

class Customer extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var Collection
     */
    protected $_customerCollection;

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
            'SOLDTOGETHER_CUSTOMER',
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

    protected function _beforeToHtml()
    {
        $this->_prepareSoldTogetherCustomerData();
        return parent::_beforeToHtml();
    }

    protected function _prepareSoldTogetherCustomerData()
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configHelper = $_objectManager->get('Swissup\SoldTogether\Helper\Config');
        $stockHelper = $_objectManager->get('Magento\CatalogInventory\Helper\Stock');

        if (!$configHelper->canShowCustomerBlock()) {
            return $this;
        }

        $product = $this->_coreRegistry->registry('product');
        /* @var $product \Magento\Catalog\Model\Product */
        $productCollection = $_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $resource = $_objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_customerCollection = $productCollection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_customerCollection);
        }
        $this->_customerCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_customerCollection->getSelect()
            ->joinInner(
                array('so' => $resource->getTableName('swissup_soldtogether_customer')),
                'so.related_id=e.entity_id and so.product_id=' . $product->getId(),
                array('soldtogether_weight' => 'so.weight')
            );

        $this->_customerCollection->getSelect()->order('soldtogether_weight ' . \Magento\Framework\DB\Select::SQL_DESC);

        $this->_customerCollection->getSelect()->limit($configHelper->getCustomerLimit());

        if ($configHelper->canShowCustomerOutOfStock()) {
            $stockHelper->addInStockFilterToCollection($this->_customerCollection);
        }

        if (!$configHelper->canShowProductWithOptions()) {
            $this->_customerCollection->getSelect()
                ->where('e.type_id IN (?)', array('simple', 'virtual'));
        }

        if ($this->_customerCollection->count() === 0 && $configHelper->canShowCustomerRandom()) {
            $this->getRandomCollection($product);
        }

        return $this;
    }

    public function getItems()
    {
        return $this->_customerCollection;
    }

    public function getRandomCollection($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configHelper = $objectManager->get('Swissup\SoldTogether\Helper\Config');
        $stockHelper = $objectManager->get('Magento\CatalogInventory\Helper\Stock');
        $category = $objectManager->get('Magento\Catalog\Model\Category');
        $category->load($product->getCategoryId());
        $this->_customerCollection = $category->getProductCollection();
        $this->_customerCollection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        $this->_addProductAttributesAndPrices($this->_customerCollection);

        $this->_customerCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_customerCollection->addAttributeToFilter('entity_id', array('nin' => array($product->getId())));

        if ($configHelper->canShowCustomerOutOfStock()) {
            $stockHelper->addInStockFilterToCollection($this->_customerCollection);
        }

        if (!$configHelper->canShowProductWithOptions()) {
            $this->_customerCollection->getSelect()
                ->where('e.type_id IN (?)', array('simple', 'virtual'));
        }

        $this->_customerCollection->getSelect()->order('rand()');
        $this->_customerCollection->getSelect()->limit($configHelper->getCustomerLimit());

        return $this;
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
