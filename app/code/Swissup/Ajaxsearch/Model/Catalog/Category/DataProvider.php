<?php

namespace Swissup\Ajaxsearch\Model\Catalog\Category;

// use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Framework\Api\FilterBuilder;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class DataProvider extends \Magento\Framework\DataObject implements DataProviderInterface
{
    const ENABLE = 'ajaxsearch/category/enable';
    const LIMIT  = 'ajaxsearch/category/limit';
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
     * Suggests Collection
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $filterPool;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SearchCriteria
     */
    protected $searchCriteria;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    protected $name = 'catalog_category_listing_data_source';

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     * @param FilterPool $filterPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        FilterPool $filterPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->queryFactory = $queryFactory;
        $this->itemFactory = $itemFactory;
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->filterPool = $filterPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
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
        // echo((string)$collection->getSelect());
        // die;
        // \Zend_Debug::dump($collection->getSize());
        $query = $this->getQuery();

        $result = [];
        foreach ($collection as $item) {
            // \Zend_Debug::dump($item->getData());
            // \Zend_Debug::dump(get_class_methods($item));
            // die;
            $resultItem = $this->itemFactory->create(
                array_merge($item->getData(), [
                    '_type' => 'category',
                    'title' => $item->getName(),
                    'num_results' => '',
                    'url' => $item->getUrl()
                    // 'url' => $this->cmsPageHelper->getPageUrl($item->getId()),
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
        $collection = $this->collectionFactory->create()
            ->addIsActiveFilter()
            ->addNameToResult()
            ->joinUrlRewrite() //apply store filter
            ->addOrderField('path')
        ;

        $limit = (int) $this->getConfig(self::LIMIT);
        if ($limit) {
            $collection->setPageSize($limit);
        }

        $searchCriteria = $this->getSearchCriteria();
        $this->filterPool->applyFilters($collection, $searchCriteria);
        return $collection;
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
     * Returns search criteria
     *
     * @return \Magento\Framework\Api\Search\SearchCriteria
     */
    public function getSearchCriteria()
    {
        if (!$this->searchCriteria) {
            $query = $this->getQuery();
            $value = $query->getQueryText();
            if ($value) {
                $filters = ['name'];
                $values = ['%' . $value . '%'];
                // $values = [$value, '%' . $value, $value . '%', '%' . $value . '%'];
                foreach ($filters as $filterAttribute) {
                    foreach ($values as $_value) {
                        $filter = $this->filterBuilder
                            ->setConditionType('like')
                            ->setField($filterAttribute)
                            ->setValue($_value)
                            ->create();
                        // \Zend_Debug::dump(get_class($filter));
                        // \Zend_Debug::dump(get_class_methods($filter));
                        $this->searchCriteriaBuilder->addFilter($filter);
                    }
                }
            }

            $this->searchCriteria = $this->searchCriteriaBuilder->create();
            $this->searchCriteria->setRequestName($this->name);
        }
        return $this->searchCriteria;
    }
}
