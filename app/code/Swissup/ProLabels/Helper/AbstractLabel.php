<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */

namespace Swissup\ProLabels\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * ProLabels Abstract Label Helper
 *
 * @author     Swissup Team <core@magentocommerce.com>
 */
class AbstractLabel extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockState;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockStateInterface $_stockState
    ) {
        $this->_scopeConfig   = $context->getScopeConfig();
        $this->_localeDate    = $localeDate;
        $this->_storeManager  = $storeManager;
        $this->_pricingHelper = $pricingHelper;
        $this->_objectManager = $objectManager;
        $this->_stockState    = $_stockState;
        $this->catalogLabel   = [];

        parent::__construct($context);
    }

    public function getLabelOutputObject($config, $product, $mode)
    {
        $labelText = $this->getLabelText($config, $product, $mode);

        $labelData = new \Magento\Framework\DataObject(
            [
                'position'   => $config['position'],
                'text'       => $labelText,
                'image'      => $config['image'],
                'custom'     => $config['custom'],
                'custom_url' => $config['custom_url']
            ]
        );
        return $labelData;
    }

    /**
     * @return Label Text
     */
    public function getLabelText($config, $product, $mode)
    {
        $variableData = array();
        preg_match_all('/#.+?#/', $config["text"], $vars);
        foreach (current($vars) as $var) {
            if (strpos($var, '#attr:') !== false) {
                $attribute = str_replace('#attr:', '', $var);
                $attribute = str_replace('#', '', $attribute);
                $attribute = $product->getResource()->getAttribute($attribute);
                $variableData[$var] = $attribute->getFrontend()->getValue($product);
                continue;
            }
            switch ($var) {
                case "#discount_percent#":
                    $variableData[$var] = $this->getDiscountPersentValue($config, $product, $mode);
                    break;
                case "#discount_amount#":
                    $variableData[$var] = $this->getDiscountAmountValue($config, $product, $mode);
                    break;
                case "#special_price#":
                    $variableData[$var] = $this->getSpecialPriceValue($config, $product, $mode);
                    break;

                case "#price#":
                    $variableData[$var] = $this->getPriceValue($config, $product, $mode);
                    break;

                case "#final_price#":
                    $variableData[$var] = $this->getFinalPriceValue($config, $product, $mode);
                    break;
                case "#stock_item#":
                    $variableData[$var] = $this->getStockItemsValue($config, $product, $mode);
                    break;
            }
        }

        return str_replace(array_keys($variableData), $variableData, $config["text"]);
    }

    /**
     * @return Get Product Discount Persent Value
     */
    public function getDiscountPersentValue($config, $product, $mode)
    {
        if ('grouped' === $product->getTypeId()) {
            $discountValue = $this->getGroupedProductDiscountPersent($product);
            $discountValue = $discountValue / $config['round_value'];
        } elseif ('bundle' === $product->getTypeId()) {
            $discountValue = $product->getSpecialPrice() / $config['round_value'];
        } else {
            $discountValue = (100 - $product->getFinalPrice() * 100 / $product->getData("price")) / $config['round_value'];
        }

        $roundMethod   = $config['round_method'];
        $discountValue = $roundMethod($discountValue);
        $discountValue = $discountValue * $config['round_value'];
        // if ('grouped' === $product->getTypeId()) {
        //     $discountValue = __('up to ') . $discountValue;
        // }

        return $discountValue;
    }

    /**
     * @return Get Product Discount Amount Value
     */
    public function getDiscountAmountValue($config, $product, $mode)
    {
        if ('grouped' === $product->getTypeId()) {
            $discountValue = $this->getGroupedProductDiscountAmount($product);
            $discountValue = $discountValue / $config['round_value'];
        } elseif ('bundle' === $product->getTypeId()) {
            $price = $product->getPriceModel()->getTotalPrices($product);
            $fullPrice = ($price[1] * 100) / ($product->getSpecialPrice());
            $discountValue = $fullPrice - $price[1];
        } else {
            $discountValue = $product->getPrice() - $product->getFinalPrice();
        }

        $store = $this->_storeManager->getStore()->getId();
        $discountValue = $discountValue / $config['round_value'];
        $roundMethod = $config['round_method'];
        $discountValue = $roundMethod($discountValue);
        $discountValue = $discountValue * $config['round_value'];
        $discountValue = $this->_pricingHelper->currencyByStore($discountValue, $store);
        $discountValue = str_replace('<span class="price">', '', $discountValue);
        $discountValue = str_replace('</span>', '', $discountValue);
        // if ('grouped' === $product->getTypeId() || 'bundle' === $product->getTypeId()) {
        //     $discountValue = __('up to ') . $discountValue;
        // }

        return $discountValue;
    }

    /**
     * @return Get Grouped Product Discount Value
     */
    public function getGroupedProductDiscountPersent($product)
    {
        $simpleProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        $price = 0;
        $finalPrice = 0;
        $maxResult = 0;
        $result = 0;
        foreach ($simpleProducts as $simpleProduct) {
            $price = $simpleProduct->getData('price');
            $calculatedPrice = $simpleProduct->getFinalPrice();
            $result = 100- ($calculatedPrice * 100 / $price);
            if (floatval($price) > floatval($calculatedPrice)) {
                if ($result > $maxResult) {
                    $maxResult = $result;
                }
            }
        }
        return $maxResult;
    }

    /**
     * @return Get Grouped Product Discount Value
     */
    public function getGroupedProductDiscountAmount($product)
    {
        $simpleProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        $price = 0;
        $finalPrice = 0;
        $maxResult = 0;
        $result = 0;
        foreach ($simpleProducts as $simpleProduct) {
            $price = $simpleProduct->getData('price');
            $calculatedPrice = $simpleProduct->getFinalPrice();
            $result = $price - $calculatedPrice;
            if (floatval($price) > floatval($calculatedPrice)) {
                if ($result > $maxResult) {
                    $maxResult = $result;
                }
            }
        }
        return $maxResult;
    }

    /**
     * @return Get Product Special Price Value
     */
    public function getSpecialPriceValue($config, $product, $mode)
    {
        if ($discountValue = $product->getSpecialPrice()) {
            $store = $this->_storeManager->getStore()->getId();
            $discountValue = $discountValue / $config['round_value'];
            $roundMethod = $config['round_method'];
            $discountValue = $roundMethod($discountValue);
            $discountValue = $discountValue * $config['round_value'];
            $discountValue = $this->_pricingHelper->currencyByStore($discountValue, $store);
            $discountValue = str_replace('<span class="price">', '', $discountValue);
            $discountValue = str_replace('</span>', '', $discountValue);
            return $discountValue;
        }
    }

    /**
     * @return Get Product Price Value
     */
    public function getPriceValue($config, $product, $mode)
    {
        $discountValue = $product->getPrice();
        $store = $this->_storeManager->getStore()->getId();
        $discountValue = $discountValue / $config['round_value'];
        $roundMethod = $config['round_method'];
        $discountValue = $roundMethod($discountValue);
        $discountValue = $discountValue * $config['round_value'];
        $discountValue = $this->_pricingHelper->currencyByStore($discountValue, $store);
        $discountValue = str_replace('<span class="price">', '', $discountValue);
        $discountValue = str_replace('</span>', '', $discountValue);
        return $discountValue;
    }

    /**
     * @return Get Product Final Price Value
     */
    public function getFinalPriceValue($config, $product, $mode)
    {
        $discountValue = $product->getFinalPrice();
        $store = $this->_storeManager->getStore()->getId();
        $discountValue = $discountValue / $config['round_value'];
        $roundMethod = $config['round_method'];
        $discountValue = $roundMethod($discountValue);
        $discountValue = $discountValue * $config['round_value'];
        $discountValue = $this->_pricingHelper->currencyByStore($discountValue, $store);
        $discountValue = str_replace('<span class="price">', '', $discountValue);
        $discountValue = str_replace('</span>', '', $discountValue);
        return $discountValue;
    }

    public function getStockItemsValue($config, $product, $mode)
    {
        if (!$product->isSalable()) {
            return false;
        }
        $simpleQty = array();
        if ('grouped' === $product->getTypeId()) {
            $childIds = $product->getTypeInstance()->getAssociatedProducts($product);
            foreach ($childIds as $simpleProduct) {
                $simpleQty[] = $this->_stockState->getStockQty($simpleProduct->getId());
            }
            $quantity = min($simpleQty);
        } elseif ('bundle' === $product->getTypeId()) {
            $optionIds = $product->getTypeInstance()->getOptionsIds($product);
            $simpleProducts = $product->getTypeInstance()->getSelectionsCollection($optionIds, $product);

            foreach ($simpleProducts as $simpleProduct) {
                $simpleQty[] = $this->_stockState->getStockQty($simpleProduct->getId());
            }
            $quantity = min($simpleQty);
        } elseif ('configurable' === $product->getTypeId()) {
            $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($simpleProducts as $simpleProduct) {
                $simpleQty[] = $this->_stockState->getStockQty($simpleProduct->getId());
            }
            $quantity = min($simpleQty);
        } else {
            $quantity = $this->_stockState->getStockQty($product->getId());
        }

        if ($quantity < $config["stock_lower"]) {
            return $quantity;
        }

        return false;
    }
}
