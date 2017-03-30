<?xml version="1.0"?>
<!--
/**
 * @author    Amasty Team
 * @copyright Copyright (c) Amasty Ltd. ( http://www.amasty.com/ )
 * @package   Amasty_Shopby
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <preference for="Magento\LayeredNavigation\Block\Navigation\FilterRenderer"     type="Amasty\Shopby\Block\Navigation\FilterRenderer"/>
    <preference for="Magento\Swatches\Block\LayeredNavigation\RenderLayered"     type="Amasty\Shopby\Block\Navigation\SwatchRenderer"/>
    <preference for="Magento\LayeredNavigation\Block\Navigation\State"
                type="Amasty\Shopby\Block\Navigation\State"
    />
    <preference for="Magento\CatalogSearch\Model\Adapter\Mysql\Plugin\Aggregation\Category\DataProvider"
                type="Amasty\Shopby\Plugin\Aggregation\Category\DataProvider"
    />
    <preference for="Magento\CatalogSearch\Controller\Result\Index"
                type="Amasty\Shopby\Controller\Search\Result\Index"
    />
    <preference for="Magento\Elasticsearch\Model\Client\Elasticsearch"
                type="Amasty\Shopby\Model\Client\Elasticsearch"
    />

    <virtualType name="Amasty\Shopby\Model\Layer\Filter\ItemFactory" type="Magento\Catalog\Model\Layer\Filter\ItemFactory">
        <arguments>
            <argument name="instanceName" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Item</argument>
        </arguments>
    </virtualType>
    <type name="Amasty\Shopby\Model\Layer\Filter\Attribute">
        <arguments>
            <argument name="filterItemFactory" xsi:type="object">Amasty\Shopby\Model\Layer\Filter\ItemFactory</argument>
        </arguments>
    </type>
    <type name="Amasty\Shopby\Model\Layer\Filter\Price">
        <arguments>
            <argument name="filterItemFactory" xsi:type="object">Amasty\Shopby\Model\Layer\Filter\ItemFactory</argument>
        </arguments>
    </type>
    <type name="Amasty\Shopby\Model\Layer\Filter\Decimal">
        <arguments>
            <argument name="filterItemFactory" xsi:type="object">Amasty\Shopby\Model\Layer\Filter\ItemFactory</argument>
        </arguments>
    </type>
    <type name="Amasty\Shopby\Model\Layer\Filter\Category">
        <arguments>
            <argument name="filterItemFactory" xsi:type="object">Amasty\Shopby\Model\Layer\Filter\ItemFactory</argument>
        </arguments>
    </type>
    <type name="Amasty\Shopby\Model\Layer\Filter\Stock">
        <arguments>
            <argument name="filterItemFactory" xsi:type="object">Amasty\Shopby\Model\Layer\Filter\ItemFactory</argument>
        </arguments>
    </type>
    <type name="Amasty\Shopby\Model\Layer\Filter\Rating">
        <arguments>
            <argument name="filterItemFactory" xsi:type="object">Amasty\Shopby\Model\Layer\Filter\ItemFactory</argument>
        </arguments>
    </type>
    <type name="Amasty\Shopby\Model\Layer\Filter\IsNew">
        <arguments>
            <argument name="filterItemFactory" xsi:type="object">Amasty\Shopby\Model\Layer\Filter\ItemFactory</argument>
        </arguments>
    </type>
    <type name="Amasty\Shopby\Model\Layer\Filter\OnSale">
        <arguments>
            <argument name="filterItemFactory" xsi:type="object">Amasty\Shopby\Model\Layer\Filter\ItemFactory</argument>
        </arguments>
    </type>
    <virtualType name="Magento\CatalogSearch\Model\ResourceModel\Fulltext\CollectionFactory" type="Magento\Catalog\Model\ResourceModel\Product\CollectionFactory">
        <arguments>
            <argument name="instanceName" xsi:type="string">Amasty\Shopby\Model\ResourceModel\Fulltext\Collection</argument>
        </arguments>
    </virtualType>

    <virtualType name="Amasty\Shopby\Model\ResourceModel\Fulltext\SearchCollection" type="Amasty\Shopby\Model\ResourceModel\Fulltext\Collection">
        <arguments>
            <argument name="searchRequestName" xsi:type="string">quick_search_container</argument>
        </arguments>
    </virtualType>

    <virtualType name="Magento\CatalogSearch\Model\ResourceModel\Fulltext\SearchCollectionFactory" type="Magento\Catalog\Model\ResourceModel\Product\CollectionFactory">
        <arguments>
            <argument name="instanceName" xsi:type="string">Amasty\Shopby\Model\ResourceModel\Fulltext\SearchCollection</argument>
        </arguments>
    </virtualType>

    <virtualType name="categoryFilterList" type="Amasty\Shopby\Model\Layer\FilterList">
        <arguments>
            <argument name="filters" xsi:type="array">
                <item name="attribute" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Attribute</item>
                <item name="price" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Price</item>
                <item name="decimal" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Decimal</item>
                <item name="category" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Category</item>
            </argument>
            <argument name="place" xsi:type="string">sidebar</argument>
        </arguments>
    </virtualType>
    <virtualType name="searchFilterList" type="Amasty\Shopby\Model\Layer\FilterList">
        <arguments>
            <argument name="filters" xsi:type="array">
                <item name="attribute" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Attribute</item>
                <item name="price" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Price</item>
                <item name="decimal" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Decimal</item>
                <item name="category" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Category</item>
            </argument>
            <argument name="place" xsi:type="string">sidebar</argument>
        </arguments>
    </virtualType>

    <virtualType name="categoryFilterListTop" type="Amasty\Shopby\Model\Layer\FilterList">
        <arguments>
            <argument name="filterableAttributes" xsi:type="object">Magento\Catalog\Model\Layer\Category\FilterableAttributeList</argument>
            <argument name="filters" xsi:type="array">
                <item name="attribute" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Attribute</item>
                <item name="price" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Price</item>
                <item name="decimal" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Decimal</item>
                <item name="category" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Category</item>
            </argument>
            <argument name="place" xsi:type="string">top</argument>
        </arguments>
    </virtualType>
    <virtualType name="searchFilterListTop" type="Amasty\Shopby\Model\Layer\FilterList">
        <arguments>
            <argument name="filterableAttributes" xsi:type="object">Magento\Catalog\Model\Layer\Search\FilterableAttributeList</argument>
            <argument name="filters" xsi:type="array">
                <item name="attribute" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Attribute</item>
                <item name="price" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Price</item>
                <item name="decimal" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Decimal</item>
                <item name="category" xsi:type="string">Amasty\Shopby\Model\Layer\Filter\Category</item>
            </argument>
            <argument name="place" xsi:type="string">top</argument>
        </arguments>
    </virtualType>


    <type name="Magento\Catalog\Controller\Category\View">
        <plugin name="Amasty_Shopby::ajaxInject" type="Amasty\Shopby\Plugin\Ajax\CategoryViewAjax" />
    </type>
    <type name="Amasty\Shopby\Controller\Search\Result\Index">
        <plugin name="Amasty_Shopby::ajaxSearchInject" type="Amasty\Shopby\Plugin\Ajax\CategoryViewAjax" />
    </type>
    <type name="Amasty\ShopbyRoot\Controller\Index\Index">
        <plugin name="Amasty_Shopby::ajaxShopbyInject" type="Amasty\Shopby\Plugin\Ajax\CategoryViewAjax" />
    </type>
    <type name="Amasty\Xlanding\Controller\Page\View">
        <plugin name="Amasty_Shopby::ajaxXlandingInject" type="Amasty\Shopby\Plugin\Ajax\CategoryViewAjax" />
    </type>

    <type name="Magento\Catalog\Block\Product\ListProduct">
        <plugin name="Amasty_Shopby::wrapProductList" type="Amasty\Shopby\Plugin\Ajax\ProductListWrapper" />
    </type>

    <type name="Magento\CatalogWidget\Block\Product\ProductsList">
        <plugin name="Amasty_Shopby::wrapCmsBlock" type="Amasty\Shopby\Plugin\Ajax\ProductListWrapper" />
    </type>

    <type name="Magento\Catalog\Block\Product\ProductList\Toolbar">
        <plugin name="Amasty_Shopby::removeAjaxParamFromToolbar" type="Amasty\Shopby\Plugin\CatalogToolbarPlugin" />
    </type>

    <type name="Magento\Theme\Block\Html\Pager">
        <plugin name="Amasty_Shopby::removeAjaxParamFromPager" type="Amasty\Shopby\Plugin\PagerPlugin" />
    </type>

    <type name="Magento\LayeredNavigation\Block\Navigation\State">
        <plugin name="Amasty_Shopby::removeAjaxParamFromState" type="Amasty\Shopby\Plugin\StatePlugin" />
    </type>

    <type name="Magento\CatalogSearch\Model\Search\IndexBuilder">
        <plugin name="Amasty_Shopby::addStockTableToSelect" type="Amasty\Shopby\Plugin\SearchIndexBuilder" />
    </type>
    <type name="Mirasvit\SearchSphinx\Model\Search\IndexBuilder">
        <plugin name="Amasty_Shopby::MirasvitSphinxAddStockTableToSelect" type="Amasty\Shopby\Plugin\SearchIndexBuilder" />
    </type>
    <type name="Mirasvit\SearchMysql\Model\Search\IndexBuilder">
        <plugin name="Amasty_Shopby::MirasvitMysqlAddStockTableToSelect" type="Amasty\Shopby\Plugin\SearchIndexBuilder" />
    </type>

    <type name="Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider">
        <plugin name="Amasty_Shopby::stockAndRatingAggregation" type="Amasty\Shopby\Plugin\AggregationDataProvider" />
    </type>

    <type name="\Magento\Catalog\Block\Category\View">
        <plugin name="amasty_shopby_catalog_category_view" type="\Amasty\Shopby\Plugin\CategoryViewPlugin" sortOrder="100"/>
    </type>

    <type name="Magento\Framework\Url\RouteParamsResolver">
        <plugin name="Amasty_Shopby::RouteParamsResolver" type="Amasty\Shopby\Plugin\RouteParamsResolverPlugin"/>
    </type>

    <type name="Magento\LayeredNavigation\Block\Navigation">
        <plugin name="Amasty_Shopby::LayeredNavigationMaxHeight" type="Amasty\Shopby\Plugin\LayeredNavigationPlugin"/>
    </type>

    <type name="\Magento\Catalog\Model\Category">
        <plugin name="amasty_shopby_catalog_category" type="\Amasty\Shopby\Plugin\CategoryPlugin" sortOrder="100"/>
    </type>

    <virtualType name="Amasty\Shopby\Block\Navigation\FilterCollapsing\Category" type="Amasty\Shopby\Block\Navigation\FilterCollapsing">
        <arguments>
            <argument name="filterList" xsi:type="object">categoryFilterList</argument>
        </arguments>
    </virtualType>
    <virtualType name="Amasty\Shopby\Block\Navigation\FilterCollapsing\Search" type="Amasty\Shopby\Block\Navigation\FilterCollapsing">
        <arguments>
            <argument name="filterList" xsi:type="object">searchFilterList</argument>
        </arguments>
    </virtualType>

    <virtualType name="Amasty\Shopby\Block\Navigation\SwatchesChoose\Category" type="Amasty\Shopby\Block\Navigation\SwatchesChoose">
        <arguments>
            <argument name="filterList" xsi:type="object">categoryFilterList</argument>
        </arguments>
    </virtualType>
    <virtualType name="Amasty\Shopby\Block\Navigation\SwatchesChoose\Search" type="Amasty\Shopby\Block\Navigation\SwatchesChoose">
        <arguments>
            <argument name="filterList" xsi:type="object">searchFilterList</argument>
        </arguments>
    </virtualType>

    <type name="Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Dynamic">
        <plugin name="Amasty_Shopby::changeAggregationForSliders" type="Amasty\Shopby\Plugin\DynamicAggregation" />
    </type>

    <type name="Magento\Framework\Search\Request\Config\FilesystemReader">
        <plugin name="Amasty_Shopby::UseAndLogicFields"  type="Amasty\Shopby\Plugin\ReaderPlugin" />
    </type>

    <type name="Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection">
        <plugin name="Amasty_Shopby::getAttributeByCode"  type="Amasty\Shopby\Plugin\AttributeCollectionPlugin" />
    </type>

    <type name="Magento\Eav\Model\Config">
        <plugin name="Amasty_Shopby::getAttributeByCodeFromConfig"  type="Amasty\Shopby\Plugin\AttributeConfigPlugin" />
    </type>

    <type name="Magento\Catalog\Controller\Product\Compare\Add">
        <plugin name="Amasty_Shopby::ProductCompareAdd" type="Amasty\Shopby\Plugin\ProductCompareAddPlugin" />
    </type>

    <type name="Magento\Cms\Controller\Index\Index">
        <plugin name="Amasty_Shopby::CmsControllerIndexIndex" type="Amasty\Shopby\Plugin\Ajax\CmsControllerIndexIndexAjax" />
    </type>

    <type name="Magento\Catalog\Model\ResourceModel\Product\Collection">
        <plugin name="Amasty_Shopby::CatalogProductCollection" type="Amasty\Shopby\Plugin\CatalogProductCollectionPlugin" />
    </type>

    <virtualType name="Amasty\Shopby\Block\Navigation\CategoryTop" type="Magento\LayeredNavigation\Block\Navigation">
        <arguments>
            <argument name="filterList" xsi:type="object">categoryFilterListTop</argument>
        </arguments>
    </virtualType>
    <virtualType name="Amasty\Shopby\Block\Navigation\SearchTop" type="Magento\LayeredNavigation\Block\Navigation">
        <arguments>
            <argument name="filterList" xsi:type="object">searchFilterListTop</argument>
        </arguments>
    </virtualType>

    <type name="Amasty\Shopby\Model\Customizer\Category">
        <arguments>
            <argument name="customizers" xsi:type="array">
                <item name="filter" xsi:type="string">Amasty\Shopby\Customizer\Category\Filter</item>
            </argument>
        </arguments>
    </type>

    <virtualType name="Amasty\Shopby\Customizer\Category\Filter" type="Amasty\Shopby\Model\Customizer\Category\Filter">
        <arguments>
            <argument name="contentHelper" xsi:type="object">Amasty\Shopby\Helper\Content</argument>
        </arguments>
    </virtualType>

    <type name="Amasty\Shopby\Helper\Content">
        <arguments>
            <argument name="filterList" xsi:type="object">categoryFilterList</argument>
        </arguments>
    </type>
    <type name="Amasty\Shopby\Helper\Data">
        <arguments>
            <argument name="filterList" xsi:type="object">categoryFilterList</argument>
        </arguments>
    </type>

    <type name="Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor">
        <plugin name="Amasty_Shopby::replaceCategoryCondition"  type="Amasty\Shopby\Plugin\MySQLFilterPreprocessor" />
    </type>
    <virtualType name="Amasty\Shopby\Block\Navigation\Cms" type="Magento\LayeredNavigation\Block\Navigation">
        <arguments>
            <argument name="filterList" xsi:type="object">categoryFilterList</argument>
        </arguments>
    </virtualType>
    <type name="Magento\Cms\Helper\Page">
        <plugin
                name="amshopby_cms_page_helper_plugin"
                type="\Amasty\Shopby\Plugin\CmsPageHelperPlugin"
                sortOrder="1"
        />
    </type>
</config>
