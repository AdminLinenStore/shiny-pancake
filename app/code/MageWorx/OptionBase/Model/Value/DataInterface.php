<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\Value;

interface DataInterface
{
    /**
     * Set array of value fields
     *
     * @param array $value
     * @return mixed
     */
    public function setValue($value);

    /**
     * Set product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function setProduct($product);

    /**
     * Retrieve module processed options values
     *
     * @return array
     */
    public function getData();
}
