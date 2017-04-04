<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Block\Product\View;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Options extends \Magento\Catalog\Block\Product\View\Options
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Tax helper
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Locale\Format $localeFormat,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $jsonEncoder,
            $option,
            $registry,
            $arrayUtils,
            $data
        );
        $this->localeFormat = $localeFormat;
        $this->priceCurrency = $priceCurrency;
        $this->taxConfig = $taxConfig;
    }

    /**
     * Get product data
     *
     * @return string (JSON)
     */
    public function getProductJsonConfig()
    {
        $product = $this->getProduct();
        $productData = $product->getData();

        return $this->_jsonEncoder->encode($productData);
    }

    /**
     * @return string (JSON)
     */
    public function getLocalePriceFormat()
    {
        $data = $this->localeFormat->getPriceFormat();
        $data['priceSymbol'] = $this->priceCurrency->getCurrency()->getCurrencySymbol();

        return $this->_jsonEncoder->encode($data);
    }

    /**
     * @param null $includeTax
     * @return float
     */
    public function getProductFinalPrice($includeTax = null)
    {
        $product = $this->getProduct();
        return $this->_catalogData->getTaxPrice(
            $product,
            $product->getFinalPrice(),
            $includeTax,
            null,
            null,
            null,
            null,
            null,
            true
        );
    }

    /**
     * @param null $includeTax
     * @return float
     */
    public function getProductRegularPrice($includeTax = null)
    {
        $product = $this->getProduct();
        return $this->_catalogData->getTaxPrice(
            $product,
            $product->getPrice(),
            $includeTax,
            null,
            null,
            null,
            null,
            null,
            true
        );
    }

    /**
     * Get type of price display from the tax config
     * Returns true if display type is both: with tax & without tax
     *
     * @return integer
     */
    public function getDisplayBothPriceWithAndWithoutTax()
    {
        $store = $this->_storeManager->getStore(null);
        $result = $this->taxConfig->getPriceDisplayType($store) == \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH;

        return (int)$result;
    }

    /**
     * Get flag: is catalog price already contains tax
     *
     * @return int
     */
    public function getCatalogPriceContainsTax()
    {
        $store = $this->_storeManager->getStore(null);
        $result = $this->taxConfig->priceIncludesTax($store);

        return (int)$result;
    }
}
