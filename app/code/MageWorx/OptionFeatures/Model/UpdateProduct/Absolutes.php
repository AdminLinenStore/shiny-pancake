<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model\UpdateProduct;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use MageWorx\OptionBase\Model\UpdateProduct\InstanceInterface;
use MageWorx\OptionFeatures\Model\ProductAttributes;

class Absolutes implements InstanceInterface
{
    /**
     * @var \MageWorx\OptionFeatures\Model\ProductAttributesFactory
     */
    protected $attributesFactory;

    /**
     * @var \MageWorx\OptionFeatures\Model\ResourceModel\ProductAttributes\CollectionFactory
     */
    protected $attributesCollectionFactory;

    /**
     * Absolutes constructor.
     * @param \MageWorx\OptionFeatures\Model\ProductAttributesFactory $attributesFactory
     * @param \MageWorx\OptionFeatures\Model\ResourceModel\ProductAttributes\CollectionFactory $collectionFactory
     */
    public function __construct(
        \MageWorx\OptionFeatures\Model\ProductAttributesFactory $attributesFactory,
        \MageWorx\OptionFeatures\Model\ResourceModel\ProductAttributes\CollectionFactory $collectionFactory
    ) {
        $this->attributesFactory = $attributesFactory;
        $this->attributesCollectionFactory = $collectionFactory;
    }

    /**
     * @param ProductCollection $collection
     * @return ProductCollection
     */
    public function joinOwnTable(ProductCollection $collection)
    {
        $tableName = ProductAttributes::TABLE_NAME;
        $select = $collection->getSelect();
        $from = $select->getPart('from');
        if (isset($from[$tableName])) {
            // Do nothing if tables already has been joined
            return $collection;
        }

        $condition = '`' . ProductCollection::MAIN_TABLE_ALIAS . '`.`entity_id' .
            '` = `' . $tableName . '`.`product_id`';
        $select->joinLeft(
            [
                $tableName => $collection->getTable($tableName)
            ],
            $condition,
            [
                'absolute_cost' => $tableName . '.absolute_cost',
                'absolute_price' => $tableName . '.absolute_price',
                'absolute_weight' => $tableName . '.absolute_weight',
            ]
        );

        return $collection;
    }

    /**
     * @param Product $product
     * @return Product
     */
    public function addOwnData(Product $product)
    {
        $item = $this->getItemByProduct($product);
        if (!$item) {
            return $product;
        }

        $product->addData($item->getTransferableData());

        return $product;
    }

    /**
     * @param Product $product
     * @return Product
     */
    public function saveOwnData(Product $product)
    {
        $item = $this->getItemByProduct($product);
        if (!$item) {
            /** @var ProductAttributes $item */
            $item = $this->attributesFactory->create();
            $item->setData('product_id', $product->getId());
        }

        $item->addData($item->getTransferableDataFromProduct($product));
        $item->getResource()->save($item);

        return $product;
    }

    /**
     * Get item by product_id
     *
     * @param Product $product
     * @return ProductAttributes|null
     */
    protected function getItemByProduct(Product $product)
    {
        /** @var \MageWorx\OptionFeatures\Model\ResourceModel\ProductAttributes\Collection $attributesCollection */
        $attributesCollection = $this->attributesCollectionFactory->create();
        /** @var \MageWorx\OptionFeatures\Model\ProductAttributes $item */
        $item = $attributesCollection->getItemByColumnValue('product_id', $product->getId());

        return $item;
    }
}
