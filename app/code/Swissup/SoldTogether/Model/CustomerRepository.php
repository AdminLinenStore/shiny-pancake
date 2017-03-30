<?php
namespace Swissup\SoldTogether\Model;

use Swissup\SoldTogether\Api\Data;
use Swissup\SoldTogether\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Swissup\SoldTogether\Model\ResourceModel\Customer as ResourceCustomer;
use Swissup\SoldTogether\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var ResourceCustomer
     */
    protected $resource;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var Data\CustomerSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Swissup\SoldTogether\Api\Data\CustomerInterfaceFactory
     */
    protected $dataCustomerFactory;

    private $storeManager;

    public function __construct(
        ResourceCustomer $resource,
        CustomerFactory $customerFactory,
        Data\CustomerInterfaceFactory $dataCustomerFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        Data\CustomerSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->customerFactory = $customerFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCustomerFactory = $dataCustomerFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    public function save(\Swissup\SoldTogether\Api\Data\CustomerInterface $customer)
    {
        try {
            $this->resource->save($customer);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $customer;
    }

    public function getById($customerId)
    {
        $customer = $this->customerFactory->create();
        $customer->load($customerId);
        if (!$customer->getId()) {
            throw new NoSuchEntityException(__('Record with id "%1" does not exist.', $customerId));
        }
        return $customer;
    }

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->customerCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $customers = [];
        /** @var Customer $customerModel */
        foreach ($collection as $customerModel) {
            $customerData = $this->dataCustomerFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $customerData,
                $customerModel->getData(),
                'Swissup\SoldTogether\Api\Data\CustomerInterface'
            );
            $customers[] = $this->dataObjectProcessor->buildOutputDataArray(
                $customerData,
                'Swissup\SoldTogether\Api\Data\CustomerInterface'
            );
        }
        $searchResults->setItems($customers);
        return $searchResults;
    }

    public function delete(\Swissup\SoldTogether\Api\Data\CustomerInterface $customer)
    {
        try {
            $this->resource->delete($customer);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById($customerId)
    {
        return $this->delete($this->getById($customerId));
    }
}
