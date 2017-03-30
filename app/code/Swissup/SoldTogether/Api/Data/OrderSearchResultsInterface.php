<?php
namespace Swissup\SoldTogether\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cms page search results.
 * @api
 */
interface OrderSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return \Swissup\SoldTogether\Api\Data\OrderInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param \Swissup\SoldTogether\Api\Data\OrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
