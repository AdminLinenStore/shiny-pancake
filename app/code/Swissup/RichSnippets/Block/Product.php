<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\RichSnippets\Block;

use Magento\Framework\View\Element\Template;

class Product extends Template
{
    /**
     * @var string
     */
    protected $_template = 'product.phtml';

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
        $this->_coreRegistry = $registry;
        $this->_imageHelper = $imageHelper;
        $this->_pricingHelper = $pricingHelper;
        $this->_objectManager = $objectManager;
        $this->_jsonEncoder = $jsonEncoder;

        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getProduct()) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    public function getJsonSnippetsProduct()
    {
        $product = $this->getProduct();
        $store = $this->_storeManager->getStore();
        $image = $this->_imageHelper
            ->init($product, 'product_page_image_small')
            ->setImageFile($product->getImage())
            ->getUrl();

        $data = array(
            '@context'              => 'http://schema.org',
            '@type'                 => 'Product',
            'name'                  => $product->getName(),
            'image'                 => $image,
            'description'           => $product->getShortDescription(),
            'sku'                   => $product->getSku(),
            'offers'                => array(
                '@type'             => 'Offer',
                'availability'      => $this->getStockStatusUrl(),
                'priceCurrency'     => $store->getCurrentCurrency()->getCode(),
                'itemCondition'     => 'http://schema.org/NewCondition'
            )
        );
        if (is_array($this->getPriceValues())) {
            $getPriceValues = $this->getPriceValues();
            $minPrice = $this->_pricingHelper->currencyByStore(min($getPriceValues), $store, false, false);
            $maxPrice = $this->_pricingHelper->currencyByStore(max($getPriceValues), $store, false, false);
            $data['offers']['@type'] = 'AggregateOffer';
            $data['offers']['lowPrice'] = $minPrice;
            $data['offers']['highPrice'] = $maxPrice;
        } else {
            $price = $this->_pricingHelper->currencyByStore($this->getPriceValues(), $store, false, false);
            $data['offers']['price'] = $price;
        }

        $review = $this->_objectManager->get('Magento\Review\Model\Review\Summary');
        $summaryData = $review->setStoreId($store->getId())->load($product->getId());
        if ((int)$summaryData->getReviewsCount() > 0) {
            $data['aggregateRating']['@type'] = 'AggregateRating';
            $data['aggregateRating']['bestRating'] = '100';
            $data['aggregateRating']['worstRating'] = '0';
            $data['aggregateRating']['ratingValue'] = $summaryData->getRatingSummary();
            $data['aggregateRating']['reviewCount'] = $summaryData->getReviewsCount();
            $data['aggregateRating']['ratingCount'] = $summaryData->getReviewsCount();
        }

        return $this->_jsonEncoder->encode($data);
    }

    public function getStockStatusUrl()
    {
        if ($this->getProduct()->isSaleable()){
            $availability = 'http://schema.org/InStock';
        } else {
            $availability = 'http://schema.org/OutOfStock';
        }
        return $availability;
    }

    /**
     * @return mixed Array with min and max values or float
     */
    public function getPriceValues()
    {
        $product     = $this->getProduct();
        $priceModel  = $product->getPriceModel();
        $productType = $product->getTypeInstance();

        if ('bundle' === $product->getTypeId()) {
            return $priceModel->getTotalPrices($product);
        }
        if ('grouped' === $product->getTypeId()) {
            $assocProducts = $productType->getAssociatedProductCollection($product)
                ->addMinimalPrice()
                ->setOrder('minimal_price', 'ASC');

            foreach ($assocProducts as $assocProduct) {
                $groupedProductsPricesArray[] = $assocProduct->getFinalPrice();
            }

            return $groupedProductsPricesArray;
        }

        $minPrice   = $product->getMinimalPrice();
        $finalPrice = $product->getFinalPrice();
        if ($minPrice && $minPrice < $finalPrice) {
            return array($minPrice, $finalPrice);
        }

        return $finalPrice;
    }
}
