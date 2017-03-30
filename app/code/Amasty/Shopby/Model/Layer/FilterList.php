<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Shopby\Model\Layer;


use Amasty\Shopby\Model\Source\FilterPlacedBlock;
use Magento\Catalog\Model\Layer\FilterableAttributeListInterface;
use Amasty\Shopby\Model\Source\VisibleInCategory;

class FilterList extends \Magento\Catalog\Model\Layer\FilterList
{
    const PLACE_SIDEBAR = 'sidebar';
    const PLACE_TOP     = 'top';

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting\Proxy
     */
    protected $filterSetting;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $currentPlace;

    /**
     * @var bool
     */
    protected $filtersMatched = false;


    /**
     * FilterList constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface          $objectManager
     * @param FilterableAttributeListInterface                   $filterableAttributes
     * @param \Amasty\Shopby\Helper\FilterSetting\Proxy          $filterSettingHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array                                              $filters
     * @param string                                             $place
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Layer\FilterableAttributeListInterface $filterableAttributes,
        \Amasty\Shopby\Helper\FilterSetting\Proxy $filterSettingHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $filters = [],
        $place = self::PLACE_SIDEBAR
    ) {
        $this->currentPlace = $place;
        $this->filterSetting = $filterSettingHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($objectManager, $filterableAttributes, $filters);
    }

    public function getFilters(\Magento\Catalog\Model\Layer $layer)
    {
        if (!count($this->filters)) {
            $filters = parent::getFilters($layer);

            $listAdditionalFilters = $this->getAdditionalFilters($layer);
            $filters = $this->insertAdditionalFilters($filters, $listAdditionalFilters);
            $filters = $this->checkAvailabilityInCurrentPlace($filters);
            $this->filters = $filters;
        }
        return $this->matchFilters($this->filters, $layer);
    }

    protected function checkAvailabilityInCurrentPlace(array $filters)
    {
        $newFilters = [];
        foreach($filters as $filter) {
            $filterSetting = $this->filterSetting->getSettingByLayerFilter($filter);
            $isAvailable = $filterSetting->getBlockPosition() == FilterPlacedBlock::POSITION_BOTH;
            $isAvailable = $isAvailable || $filterSetting->getBlockPosition() == FilterPlacedBlock::POSITION_SIDEBAR && $this->currentPlace == self::PLACE_SIDEBAR;
            $isAvailable = $isAvailable || $filterSetting->getBlockPosition() == FilterPlacedBlock::POSITION_TOP && $this->currentPlace == self::PLACE_TOP;
            if($isAvailable) {
                $newFilters[] = $filter;
            }
        }

        return $newFilters;
    }

    /**
     * @param array $listFilters
     * @param \Magento\Catalog\Model\Layer $layer
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function matchFilters(array $listFilters, \Magento\Catalog\Model\Layer $layer)
    {
        if ($layer->getState()->getData('filters') === null || $this->filtersMatched) {
            return $listFilters;
        }
        $matchedFilters = [];
        foreach($listFilters as $idx => $filter){
            $setting = $this->filterSetting->getSettingByLayerFilter($filter);

            if ($setting->getVisibleInCategories() === VisibleInCategory::ONLY_IN_SELECTED_CATEGORIES &&
                !in_array($layer->getCurrentCategory()->getId(), $setting->getCategoriesFilter())
            ){
                continue;
            } else if ($setting->getVisibleInCategories() === VisibleInCategory::HIDE_IN_SELECTED_CATEGORIES &&
                in_array($layer->getCurrentCategory()->getId(), $setting->getCategoriesFilter())
            ){
                continue;
            } else if (($attributesFilter = $setting->getAttributesFilter())
                && count($attributesFilter) > 0) {
                $stateAttributes = $this->getStateAttributesIds($layer);
                $intersects = array_intersect($attributesFilter, $stateAttributes);
                if (count($intersects) === 0){
                    continue;
                }
            } else if (($attributesOptionsFilter = $setting->getAttributesOptionsFilter())
                && count($attributesOptionsFilter) > 0) {
                $stateAttributesOptions = $this->getStateAttributesOptionsFilter($layer);
                $intersects = array_intersect($attributesOptionsFilter, $stateAttributesOptions);
                if (count($intersects) === 0){
                    continue;
                }
            }

            $matchedFilters[] = $filter;
        }

        $this->filtersMatched = true;
        $this->filters = $matchedFilters;
        return $this->filters;
    }

    /**
     * @param \Magento\Catalog\Model\Layer $layer
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getStateAttributesIds(\Magento\Catalog\Model\Layer $layer)
    {
        $ids = [];
        foreach ($layer->getState()->getFilters() as $filter) {
            if ($model = $filter->getFilter()->getData('attribute_model')) {
                $ids[] = $model->getId();
            }
        }
        return array_unique($ids);
    }

    /**
     * @param \Magento\Catalog\Model\Layer $layer
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getStateAttributesOptionsFilter(\Magento\Catalog\Model\Layer $layer)
    {
        $ids = [];
        foreach ($layer->getState()->getFilters() as $filter) {
            $ids[] = $filter->getValueString();
        }
        return array_unique($ids);
    }

    /**
     * @param \Magento\Catalog\Model\Layer $layer
     *
     * @return array
     */
    protected function getAdditionalFilters(\Magento\Catalog\Model\Layer $layer)
    {
        $additionalFilters = [];
        $isStockEnabled = $this->scopeConfig->isSetFlag('amshopby/stock_filter/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($isStockEnabled && $this->isEnabledShowOutOfStock()) {
            $additionalFilters[] = $this->objectManager->create('Amasty\Shopby\Model\Layer\Filter\Stock', ['layer'=>$layer]);
        }
        $isRatingEnabled = $this->scopeConfig->isSetFlag('amshopby/rating_filter/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($isRatingEnabled) {
            $additionalFilters[] = $this->objectManager->create('Amasty\Shopby\Model\Layer\Filter\Rating', ['layer'=>$layer]);
        }
        $isNewEnabled = $this->scopeConfig->isSetFlag('amshopby/is_new_filter/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($isNewEnabled) {
            $additionalFilters[] = $this->objectManager->create('Amasty\Shopby\Model\Layer\Filter\IsNew', ['layer'=>$layer]);
        }
        $isOnSaleEnabled = $this->scopeConfig->isSetFlag('amshopby/on_sale_filter/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($isOnSaleEnabled) {
            $additionalFilters[] = $this->objectManager->create('Amasty\Shopby\Model\Layer\Filter\OnSale', ['layer'=>$layer]);
        }
        return $additionalFilters;
    }

    protected function insertAdditionalFilters($listStandartFilters, $listAdditionalFilters)
    {
        if(count($listAdditionalFilters) == 0) {
            return $listStandartFilters;
        }
        $listNewFilters = [];
        foreach($listStandartFilters as $filter) {
            if(!$filter->hasAttributeModel()) {
                $listNewFilters[] = $filter;
                continue;
            }
            $position = $filter->getAttributeModel()->getPosition();
            foreach($listAdditionalFilters as $key=>$additionalFilter) {
                $additionalFilterPosition = $additionalFilter->getPosition();
                if($additionalFilterPosition <= $position) {
                    $listNewFilters[] = $additionalFilter;
                    unset($listAdditionalFilters[$key]);
                }
            }
            $listNewFilters[] = $filter;
        }
        $listNewFilters = array_merge($listNewFilters, $listAdditionalFilters);
        return $listNewFilters;
    }

    protected function isEnabledShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


}
