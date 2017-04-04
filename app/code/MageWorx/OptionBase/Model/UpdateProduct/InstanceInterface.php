<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Model\UpdateProduct;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

interface InstanceInterface
{
    /**
     * @param ProductCollection $collection
     * @return ProductCollection
     */
    public function joinOwnTable(ProductCollection $collection);

    /**
     * @param Product $product
     * @return Product
     */
    public function addOwnData(Product $product);

    /**
     * @param Product $product
     * @return Product
     */
    public function saveOwnData(Product $product);
}
