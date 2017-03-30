<?php
namespace Swissup\SoldTogether\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * SoldTogether order CRUD interface.
 * @api
 */
interface OrderRepositoryInterface
{
    /**
     * Save data.
     *
     * @param \Swissup\SoldTogether\Api\Data\OrderInterface $order
     * @return \Swissup\SoldTogether\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Swissup\SoldTogether\Api\Data\OrderInterface $order);

    /**
     * Retrieve data.
     *
     * @param int $relationId
     * @return \Swissup\SoldTogether\Api\Data\OrderInterface
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
     * @param \Swissup\SoldTogether\Api\Data\OrderInterface $order
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Swissup\SoldTogether\Api\Data\OrderInterface $order);

    /**
     * Delete data by ID.
     *
     * @param int $orderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($orderId);
}
