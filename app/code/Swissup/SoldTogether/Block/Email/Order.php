<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\SoldTogether\Block\Email;

class Order extends \Magento\Catalog\Block\Product\ProductList\Related
{
    /**
     * @return $this
     */
    protected function _prepareData()
    {
        if (!$order = $this->getOrder()) {
            return false;
        }
        $items = $order->getAllVisibleItems();
        $ids = array();
        foreach ($items as $item) {
            $ids[] = $item->getProductId();
        }
        /* @var $product \Magento\Catalog\Model\Product */
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $resource = $_objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_itemCollection = $productCollection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_itemCollection->getSelect()
            ->joinInner(
                array('so' => $resource->getTableName('swissup_soldtogether_order')),
                'so.related_id=e.entity_id',
                array('soldtogether_weight' => 'so.weight')
            );

        $this->_itemCollection->getSelect()
            ->where('so.product_id in (?)', $ids)
            ->order('soldtogether_weight ' . \Magento\Framework\DB\Select::SQL_DESC);

        $configHelper = $_objectManager->get('Swissup\SoldTogether\Helper\Config');
        $this->_itemCollection->getSelect()->limit($configHelper->getOrderEmailLimit());

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    public function getProductCollection()
    {
        return $this->_itemCollection;
    }
}
