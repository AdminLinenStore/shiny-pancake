<?php

namespace Swissup\SoldTogether\Api\Data;

interface OrderInterface
{
    CONST RELATION_ID  = 'relation_id';
    CONST PRODUCT_ID   = 'product_id';
    CONST RELATED_ID   = 'related_id';
    CONST STORE_ID     = 'store_id';
    CONST PRODUCT_NAME = 'product_name';
    CONST RELATED_NAME = 'related_name';
    CONST WEIGHT       = 'weight';
    CONST IS_ADMIN     = 'is_admin';

    /**
     * Get relation_id
     *
     * return int
     */
    public function getRelationId();

    /**
     * Get product_id
     *
     * return int
     */
    public function getProductId();

    /**
     * Get related_id
     *
     * return int
     */
    public function getRelatedId();

    /**
     * Get store_id
     *
     * return int
     */
    public function getStoreId();

    /**
     * Get product_name
     *
     * return string
     */
    public function getProductName();

    /**
     * Get related_name
     *
     * return string
     */
    public function getRelatedName();

    /**
     * Get weight
     *
     * return int
     */
    public function getWeight();

    /**
     * Get is_admin
     *
     * return int
     */
    public function getIsAdmin();

    /**
     * Set relation_id
     *
     * @param int $relationId
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setRelationId($relationId);

    /**
     * Set product_id
     *
     * @param int $productId
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setProductId($productId);

    /**
     * Set related_id
     *
     * @param int $relatedId
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setRelatedId($relatedId);

    /**
     * Set store_id
     *
     * @param int $storeId
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setStoreId($storeId);

    /**
     * Set product_name
     *
     * @param string $productName
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setProductName($productName);

    /**
     * Set related_name
     *
     * @param string $relatedName
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setRelatedName($relatedName);

    /**
     * Set weight
     *
     * @param int $weight
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setWeight($weight);

    /**
     * Set is_admin
     *
     * @param int $isAdmin
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setIsAdmin($isAdmin);

}
