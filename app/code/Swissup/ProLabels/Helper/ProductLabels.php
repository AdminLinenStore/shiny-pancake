<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */

namespace Swissup\ProLabels\Helper;

use Swissup\ProLabels\Helper\AbstractLabel;
use Magento\Store\Model\ScopeInterface;

/**
 * ProLabels Product Page Labels
 *
 * @author     Templates-Master Team <core@magentocommerce.com>
 */
class ProductLabels extends AbstractLabel
{
    /**
     * @return Get On Sale Label Data
     */
    public function getOnSaleLabel($product, $mode)
    {
        $isOnSaleConfig = $this->_scopeConfig->getValue("prolabels/on_sale/{$mode}", ScopeInterface::SCOPE_STORE);
        if (!$isOnSaleConfig["active"]
            || !$this->isOnSale($product)
            || !$this->validatePredefinedVariable($isOnSaleConfig, $product))
        {
            return false;
        }

        return $this->getLabelOutputObject($isOnSaleConfig, $product, $mode);
    }

    /**
     * @return Get Is New Label Data
     */
    public function getIsNewLabel($product, $mode)
    {
        $isInSaleConfig = $this->_scopeConfig->getValue("prolabels/is_new/{$mode}", ScopeInterface::SCOPE_STORE);
        if (!$isInSaleConfig["active"]
            || !$this->isNew($product)
            || !$this->validatePredefinedVariable($isInSaleConfig, $product))
        {
            return false;
        }

        return $this->getLabelOutputObject($isInSaleConfig, $product, $mode);
    }

    /**
     * @return Get Stock Label Data
     */
    public function getStockLabel($product, $mode)
    {
        $stockConfig = $this->_scopeConfig->getValue("prolabels/in_stock/{$mode}", ScopeInterface::SCOPE_STORE);
        if (!$stockConfig["active"]
            || !$this->getStockItemsValue($stockConfig, $product , $mode)
            || !$this->validatePredefinedVariable($stockConfig, $product))
        {
            return false;
        }

        return $this->getLabelOutputObject($stockConfig, $product, $mode);
    }

    /**
     * @return Get Out Of Stock Label Data
     */
    public function getOutOfStockLabel($product, $mode)
    {
        $isSalable = $product->isSalable();
        $stockConfig = $this->_scopeConfig->getValue("prolabels/out_stock/{$mode}", ScopeInterface::SCOPE_STORE);
        if (!$stockConfig["active"]
            || $isSalable
            || !$this->validatePredefinedVariable($stockConfig, $product))
        {
            return false;
        }

        return $this->getLabelOutputObject($stockConfig, $product, $mode);
    }

    /**
     * @return Check If Product Has Discount
     */
    public function isOnSale($product)
    {
        $store = $this->_storeManager->getStore()->getId();
        if ('bundle' === $product->getTypeId()) {
            if ($product->getSpecialPrice()) {
                $specialPriceFrom = $product->getSpecialFromDate();
                $specialPriceTo   = $product->getSpecialToDate();
                return $this->_localeDate->isScopeDateInInterval($store, $specialPriceFrom, $specialPriceTo);
            }
        } elseif ('grouped' === $product->getTypeId()) {
            $simpleProductIds = $product->getTypeInstance()->getAssociatedProducts($product);
            foreach($simpleProductIds as $simpleProduct) {
                if (floatval($simpleProduct->getFinalPrice()) < floatval($simpleProduct->getPrice())) {
                    return true;
                }
            }
        } elseif (floatval($product->getFinalPrice()) < floatval($product->getData("price"))) {
            return true;
        }

        return false;
    }

    /**
     * @return Check If Product Is New
     */
    public function isNew($product)
    {
        $store           = $this->_storeManager->getStore()->getId();
        $specialNewsFrom = $product->getNewsFromDate();
        $specialNewsTo   = $product->getNewsToDate();
        if ($specialNewsFrom ||  $specialNewsTo) {
            return $this->_localeDate->isScopeDateInInterval($store, $specialNewsFrom, $specialNewsTo);
        }

        return false;
    }

    public function validatePredefinedVariable($config, $product)
    {
        $productType = $product->getTypeId();
        if ('bundle' === $productType || 'grouped' === $productType) {
            preg_match_all('/#.+?#/', $config["text"], $vars);
            foreach (current($vars) as $var) {
                if (($var === '#special_price#') || ($var === '#special_date#')
                    || ($var === '#final_price#') || ($var === '#price#')) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getUploadedLabelImage($imagePath, $mode)
    {
        $baseMediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $baseMediaUrl . 'prolabels/' . $mode . "/" . $imagePath;
    }

    public function getUploadedLabelImagePath($imagePath, $mode)
    {
        $baseMediaUrl = $this->_storeManager->getStore()->getBaseMediaDir();
        return $baseMediaUrl . '/' . 'prolabels/' . $mode . "/" . $imagePath;
    }
}
