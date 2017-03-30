<?php
namespace Swissup\Askit\Api\Data;


interface VoteInterface
{
    CONST ID = 'id';
    CONST ITEM_ID = 'item_id';
    CONST CUSTOMER_ID = 'customer_id';

    /**
     * Get id
     *
     * return int
     */
    public function getId();

    /**
     * Get item_id
     *
     * return int
     */
    public function getItemId();

    /**
     * Get customer_id
     *
     * return int
     */
    public function getCustomerId();


    /**
     * Set id
     *
     * @param int $id
     * return \Swissup\Askit\Api\Data\VoteInterface
     */
    public function setId($id);

    /**
     * Set item_id
     *
     * @param int $itemId
     * return \Swissup\Askit\Api\Data\VoteInterface
     */
    public function setItemId($itemId);

    /**
     * Set customer_id
     *
     * @param int $customerId
     * return \Swissup\Askit\Api\Data\VoteInterface
     */
    public function setCustomerId($customerId);
}
