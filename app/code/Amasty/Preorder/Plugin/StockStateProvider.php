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


class StockStateProvider
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Amasty\Preorder\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Amasty\Preorder\Helper\Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->objectManager = $objectManager;
        $this->helper = $helper;
    }

    public function aroundCheckQty(\Magento\CatalogInventory\Model\StockStateProvider $subject,\Closure $closure,\Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem, $qty)
    {
        $result = $closure($stockItem, $qty);
        if ($result) {
            return $result;
        }

        $preordersEnabled = $this->helper->preordersEnabled();
        $isPreorder = $stockItem->getBackorders() == \Amasty\Preorder\Helper\Data::BACKORDERS_PREORDER_OPTION;
        $emptyQtyAllowed = $this->scopeConfig->isSetFlag('ampreorder/functional/allowemptyqty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $result = $preordersEnabled && $isPreorder && $emptyQtyAllowed;

        return $result;
    }

    public function aroundVerifyStock
    (
        \Magento\CatalogInventory\Model\StockStateProvider $subject,
        \Closure $closure,
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
    )
    {

        $result = $closure($stockItem);
        if(!$result){
            return $result;
        }

        if ($stockItem->getQty() <= $stockItem->getMinQty() && $stockItem->getBackorders() == \Amasty\Preorder\Helper\Data::BACKORDERS_PREORDER_OPTION) {
            return $this->scopeConfig->isSetFlag('ampreorder/functional/allowemptyqty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return true;
    }
}
