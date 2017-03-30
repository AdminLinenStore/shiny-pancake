<?php

namespace Swissup\SoldTogether\Model;

use Swissup\SoldTogether\Api\Data\OrderInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Order extends \Magento\Framework\Model\AbstractModel implements OrderInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'soldtogether_Order';

    /**
     * @var string
     */
    protected $_cacheTag = 'soldtogether_Order';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'soldtogether_Order';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\SoldTogether\Model\ResourceModel\Order');
    }

    public function getOrderIdsToReindex($count, $step)
    {
        return $this->_getResource()->getOrderIdsToReindex($count, $step);
    }

    public function relationExist($productId, $relatedId, $storeId)
    {
        return $this->_getResource()->relationExist($productId, $relatedId, $storeId);
    }

    public function getRelatedProductData($productId)
    {
        return $this->_getResource()->getRelatedProductData($productId);
    }

    public function deleteAllOrderRelations()
    {
        return $this->_getResource()->deleteAllOrderRelations();
    }

    public function updateProductRelations($orderData, $productId, $productName)
    {
        $this->_getResource()->deleteProductOrderRelations($productId);
        foreach ($orderData as $item) {
            $itemExist = $this->relationExist($productId, $item['id'], 0);
            if ($itemExist) {
                $this->load($itemExist);
                if ($this->getWeight() != $item['position']) {
                    $this->setIsAdmin(1)
                        ->setWeight($item['position']);
                    $this->save();
                }
            } else {
                $this->setId(null)
                    ->setProductId($productId)
                    ->setRelatedId($item['id'])
                    ->setStoreId(0)
                    ->setProductName($productName)
                    ->setRelatedName($item['name'])
                    ->setIsAdmin(1)
                    ->setWeight($item['position']);
                $this->save();
            }
        }

        return $this;
    }

    public function createNewOrderRelations($order)
    {
        $storeId = $order->getStoreId();
        $visibleItems = $order->getAllVisibleItems();
        $orderProducts = [];
        $result = [];
        if (count($visibleItems) > 1) {
            foreach ($visibleItems as $product) {
                $orderProducts[$product->getProductId()] = $product->getName();
            }
            foreach ($orderProducts as $productId => $productName) {
                foreach ($orderProducts as $relatedId => $relatedName) {
                    if ($productId == $relatedId) { continue; }
                    $result[] = [
                        'product_id'   => $productId,
                        'related_id'   => $relatedId,
                        'product_name' => $productName,
                        'related_name' => $relatedName,
                        'store_id'     => 0,
                        'weight'       => 1,
                        'is_admin'     => 0
                    ];
                }
            }
        }
        if (count($result) > 0) {
            // add data to db
            $dbData = [];
            foreach ($result as $item) {
                $itemExist = $this->relationExist($item['product_id'], $item['related_id'], $item['store_id']);
                if ($itemExist) {
                    try {
                        $this->load($itemExist);
                        if (!(int)$this->getIsAdmin()) {
                            $this->setWeight($this->getWeight() + 1);
                            $this->save();
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                } else {
                    $dbData[] = $item;
                }
            }
            if (count($dbData) > 0) {
                try {
                    $connection = $this->_getResource()->getConnection();
                    $connection->insertMultiple(
                        $this->_getResource()->getTable('swissup_soldtogether_order'), $dbData);
                } catch (\Exception $e) {
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get relation_id
     *
     * return int
     */
    public function getRelationId()
    {
        return $this->getData(self::RELATION_ID);
    }

    /**
     * Get product_id
     *
     * return int
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Get related_id
     *
     * return int
     */
    public function getRelatedId()
    {
        return $this->getData(self::RELATED_ID);
    }

    /**
     * Get store_id
     *
     * return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Get product_name
     *
     * return string
     */
    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    /**
     * Get related_name
     *
     * return string
     */
    public function getRelatedName()
    {
        return $this->getData(self::RELATED_NAME);
    }

    /**
     * Get weight
     *
     * return int
     */
    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * Get is_admin
     *
     * return int
     */
    public function getIsAdmin()
    {
        return $this->getData(self::IS_ADMIN);
    }

    /**
     * Set relation_id
     *
     * @param int $relationId
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setRelationId($relationId)
    {
        return $this->setData(self::RELATION_ID, $relationId);
    }

    /**
     * Set product_id
     *
     * @param int $productId
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Set related_id
     *
     * @param int $relatedId
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setRelatedId($relatedId)
    {
        return $this->setData(self::RELATED_ID, $relatedId);
    }

    /**
     * Set store_id
     *
     * @param int $storeId
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Set product_name
     *
     * @param int $productName
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * Set related_name
     *
     * @param int $relatedName
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setRelatedName($relatedName)
    {
        return $this->setData(self::RELATED_NAME, $relatedName);
    }

    /**
     * Set weight
     *
     * @param int $weight
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    /**
     * Set is_admin
     *
     * @param int $isAdmin
     * return \SWISSUP\SoldTogether\Api\Data\OrderInterface
     */
    public function setIsAdmin($isAdmin)
    {
        return $this->setData(self::IS_ADMIN, $isAdmin);
    }
}
