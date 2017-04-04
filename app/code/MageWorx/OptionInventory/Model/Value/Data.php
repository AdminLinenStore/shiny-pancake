<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Model\Value;

use MageWorx\OptionInventory\Helper\Stock as HelperStock;

class Data implements \MageWorx\OptionBase\Model\Value\DataInterface
{
    const KEY_QTY = 'qty';

    const KEY_MANAGE_STOCK = 'manage_stock';

    /**
     * OptionInventory Stock Helper
     *
     * @var HelperStock
     */
    protected $helperStock;

    /**
     * @var array
     */
    protected $value = null;

    /**
     * @var null|\Magento\Catalog\Model\Product
     */
    protected $product = null;

    /**
     * Data constructor.
     *
     * @param HelperStock $helperStock
     */
    public function __construct(
        HelperStock $helperStock
    ) {
    
        $this->helperStock = $helperStock;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $value = is_array($value) ? new \Magento\Framework\DataObject($value) : $value;

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

        $data[self::KEY_QTY] = $this->helperStock
            ->floatingQty($this->getValueData(self::KEY_QTY), null, $this->getProduct());

        $data[self::KEY_MANAGE_STOCK] = $this->getValueData(self::KEY_MANAGE_STOCK);

        return $data;
    }
}
