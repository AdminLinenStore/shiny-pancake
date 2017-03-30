<?php
namespace Swissup\Askit\Api\Data;

interface ItemInterface
{
    const ID            = 'id';
    const PARENT_ID     = 'parent_id';
    const ITEM_TYPE_ID  = 'item_type_id';
    const ITEM_ID       = 'item_id';
    const STORE_ID      = 'store_id';
    const CUSTOMER_ID   = 'customer_id';
    const CUSTOMER_NAME = 'customer_name';
    const EMAIL         = 'email';
    const TEXT          = 'text';
    const HINT          = 'hint';
    const STATUS        = 'status';
    const CREATED_TIME  = 'created_time';
    const UPDATE_TIME   = 'update_time';
    const IS_PRIVATE    = 'is_private';

    const STATUS_ENABLED     = 1;
    const STATUS_DISABLED    = 0;

    const STATUS_PENDING     = 1;
    const STATUS_APPROVED    = 2;
    const STATUS_DISAPPROVED = 3;
    const STATUS_CLOSE       = 4;

    const TYPE_CATALOG_PRODUCT  = 1;
    const TYPE_CATALOG_CATEGORY = 2;
    const TYPE_CMS_PAGE         = 3;


    /**
     * Get id
     *
     * return int
     */
    public function getId();

    /**
     * Get parent_id
     *
     * return int
     */
    public function getParentId();

    /**
     * Get item_type_id
     *
     * return int
     */
    public function getItemTypeId();

    /**
     * Get item_id
     *
     * return int
     */
    public function getItemId();

    /**
     * Get store_id
     *
     * return int
     */
    public function getStoreId();

    /**
     * Get customer_id
     *
     * return int
     */
    public function getCustomerId();

    /**
     * Get customer_name
     *
     * return string
     */
    public function getCustomerName();

    /**
     * Get email
     *
     * return string
     */
    public function getEmail();

    /**
     * Get text
     *
     * return string
     */
    public function getText();

    /**
     * Get hint
     *
     * return int
     */
    public function getHint();

    /**
     * Get status
     *
     * return int
     */
    public function getStatus();

    /**
     * Get created_time
     *
     * return string
     */
    public function getCreatedTime();

    /**
     * Get update_time
     *
     * return string
     */
    public function getUpdateTime();

    /**
     * Get private
     *
     * return int
     */
    public function getIsPrivate();


    /**
     * Set id
     *
     * @param int $id
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setId($id);

    /**
     * Set parent_id
     *
     * @param int $parentId
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setParentId($parentId);

    /**
     * Set item_type_id
     *
     * @param int $itemTypeId
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setItemTypeId($itemTypeId);

    /**
     * Set item_id
     *
     * @param int $itemId
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setItemId($itemId);

    /**
     * Set store_id
     *
     * @param int $storeId
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setStoreId($storeId);

    /**
     * Set customer_id
     *
     * @param int $customerId
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setCustomerId($customerId);

    /**
     * Set customer_name
     *
     * @param string $customerName
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setCustomerName($customerName);

    /**
     * Set email
     *
     * @param string $email
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setEmail($email);

    /**
     * Set text
     *
     * @param string $text
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setText($text);

    /**
     * Set hint
     *
     * @param int $hint
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setHint($hint);

    /**
     * Set status
     *
     * @param int $status
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setStatus($status);

    /**
     * Set created_time
     *
     * @param string $createdTime
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setCreatedTime($createdTime);

    /**
     * Set update_time
     *
     * @param string $updateTime
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set private
     *
     * @param int $private
     * return \Swissup\Askit\Api\Data\ItemInterface
     */
    public function setIsPrivate($private);

}
