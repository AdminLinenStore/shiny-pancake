<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Class Option.
 * This class do update custom options before save or before render.
 *
 * @package MageWorx\OptionBase\Model\Product
 */
class OptionManager
{
    /**
     * This array containes Value Data Models from all modules in APO package.
     *
     * @var array
     */
    protected $valueModels;

    /**
     * @var null|\Magento\Catalog\Model\Product
     */
    protected $product = null;

    /**
     * This array may contain product options or template options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $productAttributes = [];

    /**
     * @var array
     */
    protected $updateProductInstances = [];

    /**
     * Option constructor.
     *
     * @param array $valueModels
     * @param array $productAttributes
     * @param array $updateProductInstances
     */
    public function __construct(
        $valueModels = [],
        $productAttributes = [],
        $updateProductInstances = []
    ) {
        $this->valueModels = $valueModels;
        $this->productAttributes = $productAttributes;
        $this->updateProductInstances = $updateProductInstances;
        $this->validate();
    }

    /**
     * Validate input params
     *
     * @throws \Exception
     */
    private function validate()
    {
        foreach ($this->updateProductInstances as $instance) {
            if (!$instance instanceof \MageWorx\OptionBase\Model\UpdateProduct\InstanceInterface) {
                $msg = __(
                    'Expected \MageWorx\OptionBase\Model\UpdateProduct\InstanceInterface, got: %1',
                    get_class($instance)
                );
                throw new \Exception($msg);
            }
        }
    }

    /**
     * @return array
     */
    public function getProductAttributes()
    {
        return $this->productAttributes;
    }

    /**
     * @param null|\Magento\Catalog\Model\Product $product
     * @return $this
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
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Update custom fields data in Template Group
     *
     * @return $this
     */
    public function updateOptionsBeforeGroupSave($resultOptions)
    {
        $options = $this->getOptions();
        $product = $this->getProduct();

        foreach ($options as $optionKey => $option) {
            if (isset($resultOptions[$optionKey]['values'])) {
                $values = $option->getValues() ? $option->getValues() : [];
                foreach ($values as $valueKey => $value) {
                    $processedOptions = $this->getProcessedOptions($value, $product);
                    $resultOptionsValue = $resultOptions[$optionKey]['values'][$valueKey];
                    $resultOptions[$optionKey]['values'][$valueKey] = array_merge($resultOptionsValue, $processedOptions);
                }
            }
        }

        $this->setOptions($resultOptions);

        return $this;
    }

    /**
     * Update custom fields data in Product
     *
     * @return $this
     */
    public function updateOptionsBeforeProductSave()
    {
        $options = $this->getOptions();
        $product = $this->getProduct();

        foreach ($options as $optionKey => $option) {
            $values = isset($option['values']) ? $option['values'] : [];
            foreach ($values as $valueKey => $value) {
                $processedOptions = $this->getProcessedOptions($value, $product);
                array_merge($options[$optionKey]['values'][$valueKey], $processedOptions);
            }
        }

        $this->setOptions($options);

        return $this;
    }

    /**
     * Update custom fields data before rendere options
     *
     * @param array $resultOptions
     * @return $this
     */
    public function updateOptionsBeforeDisplay($resultOptions)
    {
        $options = $this->getOptions();
        $product = $this->getProduct();

        $i = 0;
        foreach ($options as $option) {
            if ($option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT) {
                $j = 0;
                //get current option object from $result options array
                $resultOption = $resultOptions[$i];
                foreach ($option->getValues() as $value) {
                    // get current option values (array) from option object
                    $resultValue = $resultOption->getData('optionValues');

                    // add OptionInventory fields
                    $processedOptions = $this->getProcessedOptions($value, $product);
                    $resultValue[$j] = array_merge($resultValue[$j], $processedOptions);

                    // set modified option values to current option object
                    $resultOption->setData('optionValues', $resultValue);

                    $j++;
                }
            }
            $i++;
        }

        $this->setOptions($resultOptions);

        return $this;
    }

    /**
     * Retrieve full array of processed custom options
     *
     * @param array $value
     * @param null|\Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getProcessedOptions($value, $product)
    {
        $processedOptions = [];

        foreach ($this->valueModels as $model) {
            $data = $model
                ->setValue($value)
                ->setProduct($product)
                ->getData();

            $processedOptions = array_merge($processedOptions, $data);
        }

        return $processedOptions;
    }

    /**
     * Adds product based attributes to the product
     *
     * @param Product $product
     * @return Product
     */
    public function addProductAttributes(Product $product)
    {
        /** @var \MageWorx\OptionBase\Model\UpdateProduct\InstanceInterface $instance */
        foreach ($this->updateProductInstances as $instance) {
            $instance->addOwnData($product);
        }

        return $product;
    }

    /**
     * Save product based attributes to the corresponding tables after the product was saved
     *
     * @param Product $product
     * @return Product
     */
    public function saveDataFromProductAttributes(Product $product)
    {
        /** @var \MageWorx\OptionBase\Model\UpdateProduct\InstanceInterface $instance */
        foreach ($this->updateProductInstances as $instance) {
            $instance->saveOwnData($product);
        }

        return $product;
    }

    /**
     * Join the corresponding tables from an other APO modules to the product collection
     *
     * @param ProductCollection $collection
     * @return ProductCollection
     */
    public function joinProductCollection(ProductCollection $collection)
    {
        /** @var \MageWorx\OptionBase\Model\UpdateProduct\InstanceInterface $instance */
        foreach ($this->updateProductInstances as $instance) {
            $instance->joinOwnTable($collection);
        }

        return $collection;
    }
}
