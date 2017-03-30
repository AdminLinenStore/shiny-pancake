<?php
namespace Swissup\SoldTogether\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cms page search results.
 * @api
 */
interface CustomerSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return \Swissup\SoldTogether\Api\Data\CustomerInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param \Swissup\SoldTogether\Api\Data\CustomerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
