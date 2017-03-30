<?php
namespace Swissup\SoldTogether\Model;

use Swissup\SoldTogether\Api\Data\CustomerInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Customer extends \Magento\Framework\Model\AbstractModel implements CustomerInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'soldtogether_Customer';

    /**
     * @var string
     */
    protected $_cacheTag = 'soldtogether_Customer';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'soldtogether_Customer';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\SoldTogether\Model\ResourceModel\Customer');
    }

    public function getCustomerOrderIds($count, $step)
    {
        return $this->_getResource()->getCustomerOrderIds($count, $step);
    }

    public function relationExist($productId, $relatedId, $storeId)
    {
        return $this->_getResource()->relationExist($productId, $relatedId, $storeId);
    }

    public function deleteAllCustomerRelations()
    {
        return $this->_getResource()->deleteAllCustomerRelations();
    }

    public function getRelatedProductData($productId)
    {
        return $this->_getResource()->getRelatedProductData($productId);
    }

    public function updateProductRelations($customerData, $productId, $productName)
    {
        $this->_getResource()->deleteProductCustomerRelations($productId);
        foreach ($customerData as $item) {
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

    public function createNewCustomerRelations($order)
    {
        $customerEmail = $order->getCustomerEmail();
        $storeId = $order->getStoreId();
        $orderId = $order->getId();
        $productIds = $this->_getResource()->getCustomerNewOrderIds($customerEmail, $orderId, $storeId);

        $result = [];
        $visibleItems = $order->getAllVisibleItems();

        foreach ($visibleItems as $product) {
            foreach ($productIds as $relatedId => $relatedData) {
                if ($product->getProductId() == $relatedId) { continue; }
                $result[] = [
                    'product_id'   => $product->getProductId(),
                    'related_id'   => $relatedId,
                    'product_name' => $product->getName(),
                    'related_name' => $relatedData['name'],
                    'store_id'     => 0,
                    'weight'       => 1,
                    'is_admin'     => 0
                ];
            }
        }
        if (count($result) > 0) {
            // add data to db
            $dbData = [];
            foreach ($result as $item) {
                $itemExist = $this->relationExist($item['product_id'], $item['related_id'], 0);
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
                        $this->_getResource()->getTable('swissup_soldtogether_customer'), $dbData);
                } catch (\Exception $e) {

                }
            }
        }
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
