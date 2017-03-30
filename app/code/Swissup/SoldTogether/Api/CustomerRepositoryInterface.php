<?php
namespace Swissup\SoldTogether\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * SoldTogether customer CRUD interface.
 * @api
 */
interface CustomerRepositoryInterface
{
    /**
     * Save data.
     *
     * @param \Swissup\SoldTogether\Api\Data\CustomerInterface $customer
     * @return \Swissup\SoldTogether\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Swissup\SoldTogether\Api\Data\CustomerInterface $customer);

    /**
     * Retrieve data.
     *
     * @param int $relationId
     * @return \Swissup\SoldTogether\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($relationId);

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Cms\Api\Data\PageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete data.
     *
     * @param \Swissup\SoldTogether\Api\Data\CustomerInterface $customer
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Swissup\SoldTogether\Api\Data\CustomerInterface $customer);

    /**
     * Delete data by ID.
     *
     * @param int $customerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customerId);
}
