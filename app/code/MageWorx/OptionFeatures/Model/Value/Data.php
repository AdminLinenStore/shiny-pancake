<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Value;

use MageWorx\OptionFeatures\Helper\Data as Helper;

class Data implements \MageWorx\OptionBase\Model\Value\DataInterface
{

    /**
     * @var \Magento\Framework\DataObject|array
     */
    protected $value = null;

    /**
     * @var null|\Magento\Catalog\Model\Product
     */
    protected $product = null;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $dataObjectFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\DataObject\Factory $dataObjectFactory
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $value = is_array($value) ? $this->dataObjectFactory->create(['data' => $value]) : $value;

        $this->value = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Retrieve value data by key
     *
     * @param $key
     * @return mixed
     */
    public function getValueData($key)
    {
        $data = $this->value->getData($key) ? $this->value->getData($key) : null;

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return null|\Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $data = [];

        $data[Helper::KEY_COST] = $this->getValueData(Helper::KEY_COST);
        $data[Helper::KEY_WEIGHT] = $this->getValueData(Helper::KEY_WEIGHT);

        return $data;
    }
}
