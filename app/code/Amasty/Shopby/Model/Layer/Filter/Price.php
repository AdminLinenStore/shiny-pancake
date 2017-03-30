<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\Shopby\Model\FilterSetting;
use Amasty\Shopby\Model\Layer\Filter\Traits\FromToDecimal;
use Amasty\Shopby\Model\Source\DisplayMode;


class Price extends \Magento\CatalogSearch\Model\Layer\Filter\Price
    implements \Amasty\Shopby\Api\Data\FromToFilterInterface
{
    use FromToDecimal;

    protected $settingHelper;

    protected $currencySymbol;

    protected $dataProvider;

    protected $aggregationAdapter;

    protected $facetedData;

    protected $shopbyRequest;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        \Amasty\Shopby\Helper\FilterSetting $settingHelper,
        \Amasty\Shopby\Model\Search\Adapter\Mysql\AggregationAdapter $aggregationAdapter,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        array $data = []
    ) {
        $this->settingHelper = $settingHelper;
        $this->currencySymbol = $priceCurrency->getCurrencySymbol();
        $this->dataProvider = $dataProviderFactory->create(['layer' => $layer]);
        $this->aggregationAdapter = $aggregationAdapter;
        $this->shopbyRequest = $shopbyRequest;
        parent::__construct(
            $filterItemFactory, $storeManager, $layer, $itemDataBuilder,
            $resource, $customerSession, $priceAlgorithm, $priceCurrency,
            $algorithmFactory, $dataProviderFactory, $data
        );
    }

    /**
     * @return array
     */
    public function getFromToConfig()
    {
        $config = [
            'from'          => null,
            'to'            => null,
            'min'           => null,
            'max'           => null,
            'requestVar'    => null,
            'step'          => null,
            'template'      => null];

        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);

        if ((string)$filterSetting->getDisplayMode() === (string)DisplayMode::MODE_SLIDER ||
            (string)$filterSetting->getDisplayMode() === (string)DisplayMode::MODE_FROM_TO_ONLY ||
            $filterSetting->getAddFromToWidget() === '1') {

            $facets = $this->getFacetedData();

            if (!isset($facets['data'])) {
                return $config;
            }

            $min = $this->getMin(
                    floatval($facets['data']['min']),
                    $filterSetting->getSliderMin()
                ) * $this->getCurrencyRate();
            $max = $this->getMax(
                    floatval($facets['data']['max']),
                    $filterSetting->getSliderMax()
                ) * $this->getCurrencyRate();

            if($min == $max) {
                return $config;
            }
            $from = !empty($this->getCurrentFrom()) ? floatval($this->getCurrentFrom()) : null;
            $to = !empty($this->getCurrentTo()) ? floatval($this->getCurrentTo()) : null;

            $config =
                [
                    'from' => $from,
                    'to' => $to,
                    'min' => $min,
                    'max' => $max,
                    'requestVar'    => $this->getRequestVar(),
                    'step'          => round($filterSetting->getSliderStep(), 4),
                    'template'      => !$filterSetting->getUnitsLabelUseCurrencySymbol() ? '{amount} '.$filterSetting->getUnitsLabel() : $this->currencySymbol . '{amount}',
                ];
        }
        return $config;
    }

    public function getItemsCount()
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        $ignoreRanges = $filterSetting->getDisplayMode() == DisplayMode::MODE_FROM_TO_ONLY
            || $filterSetting->getDisplayMode() == DisplayMode::MODE_SLIDER;
        $itemsCount = $ignoreRanges ? 0 : parent::getItemsCount();

        if ($itemsCount == 0) {
            /**
             * show up filter event don't have any option
             */
            $fromToConfig = $this->getFromToConfig();
            if ($fromToConfig && $fromToConfig['min'] != $fromToConfig['max']) {
                return 1;
            }

        }

        return $itemsCount;
    }

    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $facets = $this->getFacetedData();

        $data = [];
        if (count($facets) > 1) { // two range minimum
            foreach ($facets as $key => $aggregation) {
                $count = $aggregation['count'];
                if (strpos($key, '_') === false) {
                    continue;
                }
                $data[] = $this->prepareData($key, $count, $data);
            }
        }

        if (count($this->getFromToConfig()) && count($data) == 1) {
            $data = [];
        }

        return $data;
    }

    protected function prepareData($key, $count)
    {
        list($from, $to) = explode('_', $key);
        if ($from == '*') {
            $from = $this->getFrom($to);
        }
        if ($to == '*') {
            $to = '';
        }
        $label = $this->_renderRangeLabel(
            empty($from) ? 0 : $from * $this->getCurrencyRate(),
            empty($to) ? $to : $to * $this->getCurrencyRate()
        );
        $value = $from . '-' . $to . $this->dataProvider->getAdditionalRequestData();

        $data = [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from' => $from,
            'to' => $to,
        ];

        return $data;
    }



    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if($this->isApplied()) {
            return $this;
        }

        $filter = $this->shopbyRequest->getFilterParam($this);

        $request->setParam($this->getRequestVar(), $filter);
        $apply = parent::apply($request);
        if(!empty($filter) && !is_array($filter)) {
            $filterParams = explode(',', $filter);

            $validateFilter = $this->dataProvider->validateFilter($filterParams[0]);
            if (!$validateFilter) {
                return $this;
            }
            $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
            $this->setFromTo($validateFilter[0], $validateFilter[1]);
            if ($filterSetting->getDisplayMode() == DisplayMode::MODE_SLIDER) {
                $arrayRange = $this->getExtremeValues(
                    $filterSetting,
                    $this->getFacetedData(),
                    $this->getCurrencyRate()
                );

                $this->getLayer()->getProductCollection()->addFieldToFilter(
                    'price',
                    $arrayRange
                );
            }
        }

        return $apply;
    }

    protected function getFacetedData()
    {
        if(is_null($this->facetedData)) {
            /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
            $productCollection = $this->getLayer()->getProductCollection();
            $attribute = $this->getAttributeModel();
            if ($this->hasCurrentValue()) {
                $requestBuilder = clone $productCollection->getMemRequestBuilder();
                $requestBuilder->removePlaceholder($attribute->getAttributeCode() . '.from');
                $requestBuilder->removePlaceholder($attribute->getAttributeCode() . '.to');
                $queryRequest = $requestBuilder->create();
                $facets = $this->aggregationAdapter->getBucketByRequest($queryRequest, $attribute->getAttributeCode());
            } else {
                $facets = $productCollection->getFacetedData($attribute->getAttributeCode());
            }
            $this->facetedData = $facets;
        }

        return $this->facetedData;
    }
}
