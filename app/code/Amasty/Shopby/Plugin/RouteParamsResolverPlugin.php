<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Plugin;


class RouteParamsResolverPlugin
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $layer;

    /** @var \Magento\Framework\Url\QueryParamsResolverInterface  */
    protected $queryParamsResolver;

    /** @var \Amasty\Shopby\Model\Request  */
    protected $shopbyRequest;

    function __construct(
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Amasty\Shopby\Model\Resolver $amResolver,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver,
        \Amasty\Shopby\Model\Request $shopbyRequest
    ){
        $this->layer = $amResolver->loadFromParent($layerResolver)->get();
        $this->queryParamsResolver = $queryParamsResolver;
        $this->shopbyRequest = $shopbyRequest;
    }

    /**
     * @param \Magento\Framework\Url\RouteParamsResolver $subject
     * @param \Closure $proceed
     * @param array $data
     * @param bool|true $unsetOldParams
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundSetRouteParams(
        \Magento\Framework\Url\RouteParamsResolver $subject,
        \Closure $proceed,
        array $data,
        $unsetOldParams = true
    ){
        $result = $proceed($data, $unsetOldParams);

        if (array_key_exists('_current', $data)){
            $queryParams = $this->queryParamsResolver->getQueryParams();

            $filters = $this->layer->getState()->getFilters();

            foreach($filters as $filter) {
                $data = $this->shopbyRequest->getFilterParam($filter->getFilter());
                if (!empty($data)) {
                    $queryParams[$filter->getFilter()->getRequestVar()] = $data;
                }
            }

            $queryParams[\Amasty\Shopby\Block\Navigation\UrlModifier::VAR_REPLACE_URL] = null;;
            $queryParams['amshopby'] = null;

            $this->queryParamsResolver->addQueryParams($queryParams);
        }

        return $result;
    }
}