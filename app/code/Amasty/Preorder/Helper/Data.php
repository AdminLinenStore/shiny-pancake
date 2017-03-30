<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Preorder\Helper;


use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BACKORDERS_PREORDER_OPTION = 101;

    protected $isOrderProcessing = false;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(Context $context, \Magento\Framework\ObjectManagerInterface $objectManager)
    {
        parent::__construct($context);
        $this->objectManager = $objectManager;
    }


    public function checkNewOrder(\Magento\Sales\Model\Order $order)
    {
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderPreorder $orderPreorderResource */
        $orderPreorderResource = $this->objectManager->get('Amasty\Preorder\Model\OrderPreorder')->getResource();

        $alreadyProcessed = $order->getId() && $orderPreorderResource->getIsOrderProcessed($order->getId());
        if (!$alreadyProcessed) {
            if (is_null($order->getId())) {
                $order->save();
            }

            $this->processNewOrder($order);
        }

        // Will work for normal email flow only. Deprecated.
        if ($this->getOrderIsPreorderFlag($order)) {
            $order->setData('preorder_warning', $orderPreorderResource->getWarningByOrderId($order->getId()));
        }
    }

    protected function processNewOrder(\Magento\Sales\Model\Order $order)
    {
        $this->isOrderProcessing = true;
        /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection */
        $itemCollection = $order->getItemsCollection();

        $orderIsPreorder = false;
        foreach ($itemCollection as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            $orderItemIsPreorder = $this->getOrderItemIsPreorder($item);
            $this->saveOrderItemPreorderFlag($item, $orderItemIsPreorder);

            $orderIsPreorder |= $orderItemIsPreorder;
        }

        /** @var \Amasty\Preorder\Model\OrderPreorder $orderPreorder */
        $orderPreorder = $this->objectManager->create('Amasty\Preorder\Model\OrderPreorder');

        $orderPreorder->setOrderId($order->getId());
        $orderPreorder->setIsPreorder($orderIsPreorder);
        if ($orderIsPreorder) {
            $warningText = $this->getCurrentStoreConfig('ampreorder/general/orderpreorderwarning');
            $orderPreorder->setWarning($warningText);
        }

        $orderPreorder->save();
    }

    protected function getOrderItemIsPreorder(\Magento\Sales\Model\Order\Item $orderItem)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $orderItem->getProduct();
        $result = $this->getIsProductPreorder($product);

        if (!$result) {
            foreach($orderItem->getChildrenItems() as $childItem) {
                $result = $this->getOrderItemIsPreorder($childItem);
                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }

    protected function saveOrderItemPreorderFlag(\Magento\Sales\Model\Order\Item $orderItem, $isPreorder)
    {
        /** @var \Amasty\Preorder\Model\OrderItemPreorder $orderItemPreorder */
        $orderItemPreorder = $this->objectManager->create('Amasty\Preorder\Model\OrderItemPreorder');

        $orderItemPreorder->setOrderItemId($orderItem->getId());
        $orderItemPreorder->setIsPreorder($isPreorder);

        $orderItemPreorder->save();
    }

    public function getQuoteItemIsPreorder(\Magento\Quote\Model\Quote\Item $item, $qtyMultiplier = 1)
    {
        $product = $item->getProduct();
        $qty = $item->getQty() * $qtyMultiplier;

        if ($product->isComposite()) {
            $productTypeInstance = $product->getTypeInstance();

            if ($productTypeInstance instanceof \Magento\ConfigurableProduct\Model\Product\Type\Configurable) {
                /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */

                /** @var \Magento\Quote\Model\Quote\Item\Option $option */
                $option = $item->getOptionByCode('simple_product');
                $simpleProduct = $option->getProduct();
                if (!$simpleProduct instanceof \Magento\Catalog\Model\Product) {
                    return false;
                }
                return $this->getIsSimpleProductPreorder($simpleProduct, $qty);
            }

            if ($productTypeInstance instanceof \Magento\Bundle\Model\Product\Type) {
                /** @var \Magento\Bundle\Model\Product\Type $productTypeInstance */

                $isPreorder = false;
                foreach ($item->getChildren() as $childItem) {
                    if ($this->getQuoteItemIsPreorder($childItem, $qty)) {
                        $isPreorder = true;
                        break;
                    }
                }
                return $isPreorder;
            }
        } else {
            return $this->getIsSimpleProductPreorder($product, $qty);
        }

        return false;
    }

    public function getIsProductPreorder(\Magento\Catalog\Model\Product $product)
    {
        if(is_null($product->getIsPreorder())) {
            if ($product->isComposite()) {
                $result = $this->getIsCompositeProductPreorder($product);
            } else {
                $result = $this->getIsSimpleProductPreorder($product);
            }
            $product->setIsPreorder($result);
        }

        return $product->getIsPreorder();
    }

    protected function getIsCompositeProductPreorder(\Magento\Catalog\Model\Product $product)
    {
        if (!$this->getCurrentStoreConfig('ampreorder/additional/discovercompositeoptions'))
        {
            // We never know what options customer will select
            return false;
        }

        $typeId = $product->getTypeId();
        $typeInstance = $product->getTypeInstance();

        switch ($typeId) {
            case 'grouped':
                $result = $this->getIsGroupedProductPreorder($typeInstance, $product);
                break;

            case 'configurable':
                $result = $this->getIsConfigurableProductPreorder($typeInstance, $product);
                break;

            case 'bundle':
                $result = $this->getIsBundleProductPreorder($typeInstance, $product);
                break;

            default:
                //Mage::log('Cannot determinate pre-order status of product of unknown product type: ' . $typeId, Zend_Log::WARN);
                $result = false;
        }

        // Still have no implementation for bundles
        return $result;
    }

    protected function getIsGroupedProductPreorder(\Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance, \Magento\Catalog\Model\Product $product)
    {
        $elementaryProducts = $typeInstance->getAssociatedProducts($product);

        if (count($elementaryProducts) == 0) {
            return false;
        }

        $result = true; // for a while
        foreach ($elementaryProducts as $elementary) {
            if (!$this->getIsSimpleProductPreorder($elementary)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    protected function getIsConfigurableProductPreorder(\Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance, \Magento\Catalog\Model\Product $product)
    {
        $elementaryProducts = $typeInstance->getUsedProducts($product);

        if (count($elementaryProducts) == 0) {
            return false;
        }

        $result = true; // for a while
        foreach ($elementaryProducts as $elementary) {
            /** @var \Magento\Catalog\Model\Product $elementary */
            if (!$this->getIsSimpleProductPreorder($elementary)) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    protected function getIsBundleProductPreorder(\Magento\Bundle\Model\Product\Type $typeInstance, \Magento\Catalog\Model\Product $product)
    {
        $optionIds = array();
        $optionSelectionCounts = array();
        $optionPreorder = array();

        $options = $typeInstance->getOptionsCollection($product);
        foreach ($options as $option) {
            /** @var \Magento\Bundle\Model\Option $option */
            if (!$option->getRequired()) {
                continue;
            }

            $id = $option->getId();
            $optionIds[] = $id;
            $optionSelectionCounts[$id] = 0; // for a while
            $optionPreorder[$id] = true; // for a while
        }
        if (!$optionIds) {
            return false;
        }

        $selections = $typeInstance->getSelectionsCollection($optionIds, $product);
        $products = $this->getProductCollectionBySelectionsCollection($selections);
        foreach ($selections as $selection) {
            /** @var \Magento\Bundle\Model\Selection $selection */

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $products->getItemById($selection->getProductId());

            $isPreorder = $this->getIsSimpleProductPreorder($product);
            $optionId = $selection->getOptionId();
            $optionSelectionCounts[$optionId]++;
            if (!$isPreorder) {
                $optionPreorder[$optionId] = false;
            }
        }

        $result = false; // for a while
        foreach ($optionPreorder as $id => $isPreorder) {
            if ($isPreorder && $optionSelectionCounts[$id] > 0) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    protected function getProductCollectionBySelectionsCollection($selections)
    {
        $productIds = array();
        foreach ($selections as $selection) {
            /** @var \Magento\Bundle\Model\Selection $selection */
            $productIds[] = $selection->getProductId();
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->objectManager->create('Magento\Catalog\Model\Product')->getCollection();
        $collection->addFieldToFilter('entity_id', array('in', $productIds));

        return $collection;
    }

    protected function getIsSimpleProductPreorder(\Magento\Catalog\Model\Product $product, $requiredQty = 0)
    {
        /** @var \Magento\CatalogInventory\Model\StockRegistry $inventoryRegistry */
        $inventoryRegistry = $this->objectManager->get('Magento\CatalogInventory\Model\StockRegistry');
        /** @var \Magento\CatalogInventory\Model\Stock\Item $inventory */
        $inventory = $inventoryRegistry->getStockItem($product->getId());

        $isPreorder = $inventory->getBackorders() == self::BACKORDERS_PREORDER_OPTION;
        $minimalCount = $this->isOrderProcessing ? 0 : $requiredQty;

        $disabledByQty = $this->disableForPositiveQty() && $inventory->getQty() > $minimalCount;

        $result = $isPreorder && !$disabledByQty;

        return $result;
    }

    public function getOrderIsPreorderFlagByIncrementId($incrementId)
    {
        // finally convert back to string to optimize SQL query
        $incrementId = ''. (int)$incrementId;

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');
        $order->load($incrementId, 'increment_id');

        if (!$order->getId()) {
            $message = 'Preorder: Cannot load order by incrementId = ' . $incrementId;
            //Mage::log($message, Zend_Log::ALERT);
            return false;
        }

        return $this->getOrderIsPreorderFlag($order);
    }

    public function getOrderIsPreorderFlag(\Magento\Sales\Model\Order $order)
    {
        if (is_null($order)) {
            //Mage::log('Preorder: Cannot load preorder flag for null order. Processing as a regular order.', Zend_Log::ALERT);
            return false;
        }
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderPreorder $orderPreorderResource */
        $orderPreorderResource = $this->objectManager->get('Amasty\Preorder\Model\OrderPreorder')->getResource();
        return $orderPreorderResource->getOrderIsPreorderFlag($order->getId());
    }

    public function getOrderPreorderWarning($orderId)
    {
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderPreorder $orderPreorderResource */
        $orderPreorderResource = $this->objectManager->get('Amasty\Preorder\Model\OrderPreorder')->getResource();
        $warning = $orderPreorderResource->getWarningByOrderId($orderId);
        if (is_null($warning)) {
            $warning = $this->getCurrentStoreConfig('ampreorder/general/orderpreorderwarning');
        }

        return $warning;
    }

    public function getOrderItemIsPreorderFlag($itemId)
    {
        /** @var \Amasty\Preorder\Model\ResourceModel\OrderItemPreorder\Collection $orderItemPreorderCollection */
        $orderItemPreorderCollection = $this->objectManager->get('Amasty\Preorder\Model\OrderItemPreorder')->getCollection();
        $orderItemPreorderCollection->addFieldToFilter('order_item_id', $itemId);
        $orderItemPreorderCollection->addFieldToSelect('is_preorder');

        /** @var Amasty_Preorder_Model_Order_Preorder $orderItemPreorder */
        $orderItemPreorder = $orderItemPreorderCollection->getFirstItem();

        return is_object($orderItemPreorder) ? $orderItemPreorder->getIsPreorder() : false;
    }

    public function getQuoteItemPreorderNote(\Magento\Quote\Model\Quote\Item $quoteItem)
    {
        if ($quoteItem->getProductType() == 'configurable') {
            $option = $quoteItem->getOptionByCode('simple_product');
            $simpleProduct = $option->getProduct();
            return $this->getProductPreorderNote($simpleProduct);
        } else {
            return $this->getProductPreorderNote($quoteItem->getProduct());
        }
    }

    public function getProductPreorderNote(\Magento\Catalog\Model\Product $product)
    {
        $template = $product->getData('amasty_preorder_note');
        if (is_null($template)) {
            $resource = $product->getResource();
            $template = $resource->getAttributeRawValue($product->getId(), 'amasty_preorder_note', $product->getStoreId());
        }

        if ($template == "") {
            $template = $this->getCurrentStoreConfig('ampreorder/general/defaultpreordernote');
        }

        /** @var \Amasty\Preorder\Helper\Templater $templater */
        $templater = $this->objectManager->get('Amasty\Preorder\Helper\Templater');
        $note = $templater->process($template, $product);

        return $note;
    }

    public function getProductPreorderCartLabel(\Magento\Catalog\Model\Product $product)
    {
        $template = $product->getData('amasty_preorder_cart_label');
        if (is_null($template)) {
            $resource = $product->getResource();
            $template = $resource->getAttributeRawValue($product->getId(), 'amasty_preorder_cart_label', $product->getStoreId());
        }

        if ($template == "") {
            $template = $this->getCurrentStoreConfig('ampreorder/general/addtocartbuttontext');
        }

        /** @var \Amasty\Preorder\Helper\Templater $templater */
        $templater = $this->objectManager->get('Amasty\Preorder\Helper\Templater');
        $note = $templater->process($template, $product);

        return $note;
    }

    public function getDefaultPreorderCartLabel()
    {
        return $this->getCurrentStoreConfig('ampreorder/general/addtocartbuttontext');
    }

    public function preordersEnabled()
    {
        return $this->getCurrentStoreConfig('ampreorder/functional/enabled');
    }

    public function disableForPositiveQty()
    {
        return $this->getCurrentStoreConfig('ampreorder/functional/allowemptyqty') && $this->getCurrentStoreConfig('ampreorder/functional/disableforpositiveqty');
    }

    protected function getCurrentStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        /** @var Mage_Adminhtml_Model_Sales_Order_Create $adminOrder */
        /*$adminOrder = Mage::getSingleton('adminhtml/sales_order_create');
        $store = is_object($adminOrder) ? $adminOrder->getSession()->getStore() : Mage::app()->getStore();
        return $store->getConfig($path);*/
    }
}
