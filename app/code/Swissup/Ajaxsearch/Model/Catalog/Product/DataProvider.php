<?php

namespace Swissup\Ajaxsearch\Model\Catalog\Product;

use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class DataProvider extends \Magento\Framework\DataObject implements DataProviderInterface
{
    const ENABLE = 'ajaxsearch/product/enable';
    const LIMIT  = 'ajaxsearch/product/limit';

    /**
     * Query factory
     *
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * Autocomplete result item factory
     *
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $productCollection;

    /**
     * Default Order field
     *
     * @var string
     */
    protected $orderField = null;

    /**
     * Default direction
     *
     * @var string
     */
    protected $direction = 'desc';//ProductList::DEFAULT_SORT_DIRECTION;

    /**
     * @var ToolbarModel
     */
    protected $toolbarModel;

    /**
     * @var ProductList
     */
    protected $productListHelper;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Layer\Search $catalogLayer
     * @param ToolbarModel $toolbarModel
     * @param ProductList $productListHelper
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Layer\Search $catalogLayer,
        ToolbarModel $toolbarModel,
        ProductList $productListHelper,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
    ) {
        $this->queryFactory = $queryFactory;
        $this->itemFactory = $itemFactory;
        $this->scopeConfig = $scopeConfig;
        $this->catalogLayer = $catalogLayer;
        $this->toolbarModel = $toolbarModel;
        $this->productListHelper = $productListHelper;
        $this->imageBuilder = $imageBuilder;
    }

    /**
     *
     * @param  string $key
     * @return string
     */
    protected function getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $enable = (bool) $this->getConfig(self::ENABLE);
        if (!$enable) {
            return [];
        }
        $collection = $this->getSuggestCollection();
        // \Zend_Debug::dump(get_class($collection));
        // \Zend_Debug::dump($collection->getSize());
        // \Zend_Debug::dump(get_class($this->queryFactory->get()));
        // die;
        // $query = $this->queryFactory->get()->getQueryText();
        $query = $this->getQuery();

        // $query->saveNumResults($collection->getSize());
        // $query->saveIncrementalPopularity();

        // $_select = (string) $collection->getSelect();
        $result = [
            // $this->itemFactory->create([
            //     '_type' => 'debug',
            //     '_query' => $query->getQueryText(),
            //     '_class' => get_class($collection),
            //     '_num_results' => $collection->getSize(),
            //     '_select' => md5($_select) .  '  ' . $_select,

            // ])
        ];
        foreach ($collection as $item) {
            // \Zend_Debug::dump(get_class($item));
            // \Zend_Debug::dump($item->getData());
            // \Zend_Debug::dump(get_class_methods($item));
            // die;
            $item = $item->load($item->getId());
            $resultItem = $this->itemFactory->create(
                array_merge($item->getData(), [
                    // '_debug' => [
                    //     'class' => get_class($item),
                    //     '_data' => $item->getData(),
                    //     'methods' => get_class_methods($item),
                    // ],
                    '_type' => 'product',
                    'title' => $item->getName(),
                    'num_results' => '',
                    'image' => $this->getImage($item)->toHtml(),
                    'product_url' => $item->getProductUrl(),
                ])
            );
            if ($resultItem->getTitle() == $query->getQueryText()) {
                array_unshift($result, $resultItem);
            } else {
                $result[] = $resultItem;
            }
        }
        // $result[] = $this->itemFactory->create([
        //     'title' => '',
        //     'num_results' => '',
        //     '_type' => 'debug',
        //     '_select' => (string) $collection->getSelect()
        // ]);
        return $result;
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return Collection
     */
    private function getSuggestCollection()
    {
        // return $this->queryFactory->get()->getSuggestCollection();
        return $this->getProductCollection();
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    protected function getProductCollection()
    {
        if ($this->productCollection === null) {
            /* @var $layer \Magento\Catalog\Model\Layer */
            $layer = $this->getLayer();

            // if ($this->getShowRootCategory()) {
            //     $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
            // }

            // // if this is a product view page
            // if ($this->_coreRegistry->registry('product')) {
            //     // get collection of categories this product is associated with
            //     $categories = $this->_coreRegistry->registry('product')
            //         ->getCategoryCollection()->setPage(1, 1)
            //         ->load();
            //     // if the product is associated with any category
            //     if ($categories->count()) {
            //         // show products from this category
            //         $this->setCategoryId(current($categories->getIterator()));
            //     }
            // }

            // $origCategory = null;
            // if ($this->getCategoryId()) {
            //     try {
            //         $category = $this->categoryRepository->get($this->getCategoryId());
            //     } catch (NoSuchEntityException $e) {
            //         $category = null;
            //     }

            //     if ($category) {
            //         $origCategory = $layer->getCurrentCategory();
            //         $layer->setCurrentCategory($category);
            //     }
            // }
            $this->productCollection = $layer->getProductCollection();

            $this->productCollection->loadWithFilter();
            // $this->productCollection->getData();

            // \Zend_Debug::dump(__METHOD__);
            // \Zend_Debug::dump(get_class($layer));
            // \Zend_Debug::dump(get_class($this->productCollection));
            // \Zend_Debug::dump((string) $this->productCollection->getSelect());
            // die;

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            // toolbar setcollection emulation
            //
            // $this->productCollection->setCurPage($this->getCurrentPage());
            $limit = (int) $this->getConfig(self::LIMIT);
            if ($limit) {
                $this->productCollection->setPageSize($limit);
            }
            if ($this->getCurrentOrder()) {
                $this->productCollection->setOrder(
                    $this->getCurrentOrder(),
                    $this->getCurrentDirection()
                );
            }

            // if ($origCategory) {
            //     $layer->setCurrentCategory($origCategory);
            // }
        }

        return $this->productCollection;
    }

    /**
     * Prepare Sort By fields from Category Data
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Block\Product\ListProduct
     */
    public function prepareSortableFieldsByCategory($category)
    {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($category->getAvailableSortByOptions());
        }
        $availableOrders = $this->getAvailableOrders();

        if (!$this->getSortBy()) {
            $categorySortBy = $this->getDefaultSortBy() ?: $category->getDefaultSortBy();
            if ($categorySortBy) {
                // if (!$availableOrders) {
                //     $availableOrders = $this->getConfig()->getAttributeUsedForSortByArray();
                // }
                if (isset($availableOrders[$categorySortBy])) {
                    $this->setDefaultOrder($categorySortBy);
                }
            }
        }

        return $this;
    }

    /**
     * Get grid products sort order field
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrders();
        $defaultOrder = $this->getOrderField();
        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->toolbarModel->getOrder();
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }

        $this->setData('_current_grid_order', $order);
        return $order;
    }

    /**
     * Get order field
     *
     * @return null|string
     */
    protected function getOrderField()
    {
        if ($this->orderField === null) {
            $this->orderField = $this->productListHelper->getDefaultSortField();
        }
        return $this->orderField;
    }

    /**
     * Set default Order field
     *
     * @param string $field
     * @return $this
     */
    public function setDefaultOrder($field)
    {
        $availableOrders = $this->getAvailableOrders();
        if (isset($availableOrders[$field])) {
            $this->orderField = $field;
        }
        return $this;
    }

    /**
     * Retrieve current direction
     *
     * @return string
     */
    public function getCurrentDirection()
    {
        $dir = $this->_getData('_current_grid_direction');
        if ($dir) {
            return $dir;
        }

        $directions = ['asc', 'desc'];
        $dir = strtolower($this->toolbarModel->getDirection());
        if (!$dir || !in_array($dir, $directions)) {
            $dir = $this->direction;
        }

        $this->setData('_current_grid_direction', $dir);
        return $dir;
    }

    /**
     * Get catalog layer model
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->catalogLayer;
    }

    /**
     * Get Query object
     *
     * @return \Magento\Search\Model\Query
     */
    public function getQuery()
    {
        return $this->queryFactory->get();
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId = 'product_page_image_small', $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create()
            ->setTemplate('Swissup_Ajaxsearch::product/image.phtml');
    }
}
