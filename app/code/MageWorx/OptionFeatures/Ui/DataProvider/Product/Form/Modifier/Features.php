<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier\ModifierInterface;
use MageWorx\OptionFeatures\Helper\Data as Helper;

class Features extends AbstractModifier implements ModifierInterface
{
    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param ArrayManager $arrayManager
     * @param StoreManagerInterface $storeManager
     * @param LocatorInterface $locator
     * @param Helper $helper
     */
    public function __construct(
        ArrayManager $arrayManager,
        StoreManagerInterface $storeManager,
        LocatorInterface $locator,
        Helper $helper
    ) {
        $this->arrayManager = $arrayManager;
        $this->storeManager = $storeManager;
        $this->locator = $locator;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->locator->getProduct();
        if (!$product || !$product->getId()) {
            return $data;
        }

        return array_replace_recursive(
            $data,
            [
                $product->getId() => [
                    static::DATA_SOURCE_DEFAULT => [
                        Helper::KEY_ABSOLUTE_COST => $product->getData(Helper::KEY_ABSOLUTE_COST),
                        Helper::KEY_ABSOLUTE_WEIGHT => $product->getData(Helper::KEY_ABSOLUTE_WEIGHT),
                        Helper::KEY_ABSOLUTE_PRICE => $product->getData(Helper::KEY_ABSOLUTE_PRICE),
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->addFeaturesFields();

        return $this->meta;
    }

    /**
     * Adds features fields to the meta-data
     */
    protected function addFeaturesFields()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

        // Add fields to the values
        $valueFeaturesFields = $this->getValueFeaturesFieldsConfig();
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'],
            $valueFeaturesFields
        );

        // Add fields to the option
        $optionFeaturesFields = $this->getOptionFeaturesFieldsConfig();
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $optionFeaturesFields
        );

        // Add fields to the options container
        $productFeaturesFields = $this->getProductFeaturesFieldsConfig();
        $this->meta[$groupCustomOptionsName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children'],
            $productFeaturesFields
        );
    }

    /**
     * The custom option value fields config
     *
     * @return array
     */
    protected function getValueFeaturesFieldsConfig()
    {
        $fields = [];
        if ($this->helper->isCostEnabled()) {
            $fields[Helper::KEY_COST] = $this->getCostConfig(130);
        }
        if ($this->helper->isWeightEnabled()) {
            $fields[Helper::KEY_WEIGHT] = $this->getWeightConfig(140);
        }

        return $fields;
    }

    /**
     * Is default field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getIsDefaultConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Default'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => Helper::KEY_IS_DEFAULT,
                        'dataType' => Number::NAME,
                        'prefer' => 'toggle',
                        'valueMap' => [
                            'true' => Helper::IS_DEFAULT_TRUE,
                            'false' => Helper::IS_DEFAULT_FALSE,
                        ],
                        'fit' => true,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Cost field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getCostConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Cost'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => Helper::KEY_COST,
                        'dataType' => Number::NAME,
                        'addbefore' => $this->getBaseCurrencySymbol(),
                        'validation' => [
                            'validate-number' => true,
                            'validate-zero-or-greater' => true,
                        ],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Weight field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getWeightConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Weight'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => Helper::KEY_WEIGHT,
                        'dataType' => Number::NAME,
                        'validation' => [
                            'validate-number' => true,
                            'validate-zero-or-greater' => true,
                        ],
                        'sortOrder' => $sortOrder,
                        'additionalClasses' => 'admin__field-small',
                        'addafter' => $this->getWeightUnit(),
                        'imports' => [
                            'disabled' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT
                                . '.product_has_weight:value',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * The custom option fields config
     *
     * @return array
     */
    protected function getOptionFeaturesFieldsConfig()
    {
        $fields = [];

        if ($this->helper->isOneTimeEnabled()) {
            $fields[Helper::KEY_ONE_TIME] = $this->getOneTimeConfig(60);
        }

        return $fields;
    }

    /**
     * Enable qty input (for option) field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getQtyInputConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Qty Input'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => Helper::KEY_QTY_INPUT,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => Helper::QTY_INPUT_TRUE,
                            'false' => Helper::QTY_INPUT_FALSE,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Is One Time Option field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getOneTimeConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('One Time'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => Helper::KEY_ONE_TIME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => Helper::ONE_TIME_TRUE,
                            'false' => Helper::ONE_TIME_FALSE,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * The product based features config (see `catalog_product_entity` table)
     *
     * @return array
     */
    protected function getProductFeaturesFieldsConfig()
    {

        $children =  [];
        if ($this->helper->isAbsoluteCostEnabled()) {
            $children[Helper::KEY_ABSOLUTE_COST] = $this->getAbsoluteCostConfig(5);
        }
        if ($this->helper->isAbsoluteWeightEnabled()) {
            $children[Helper::KEY_ABSOLUTE_WEIGHT] = $this->getAbsoluteWeightConfig(7);
        }
        if ($this->helper->isAbsolutePriceEnabled()) {
            $children[Helper::KEY_ABSOLUTE_PRICE] = $this->getAbsolutePriceConfig(9);
        }

        $fields = [
            'global_config_container' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'formElement' => Container::NAME,
                            'component' => 'Magento_Ui/js/form/components/group',
                            'breakLine' => false,
                            'showLabel' => false,
                            'additionalClasses' =>
                                'admin__field-control admin__control-grouped admin__field-group-columns',
                            'sortOrder' => 10,
                        ],
                    ],
                ],
                'children' => $children
            ],
        ];

        return $fields;
    }

    /**
     * Absolute Cost field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getAbsoluteCostConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Absolute Cost'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => Helper::KEY_ABSOLUTE_COST,
                        'dataType' => Number::NAME,
                        'prefer' => 'toggle',
                        'valueMap' => [
                            'true' => Helper::ABSOLUTE_COST_TRUE,
                            'false' => Helper::ABSOLUTE_COST_FALSE,
                        ],
                        'fit' => true,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Absolute Weight field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getAbsoluteWeightConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Absolute Weight'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => Helper::KEY_ABSOLUTE_WEIGHT,
                        'dataType' => Number::NAME,
                        'prefer' => 'toggle',
                        'valueMap' => [
                            'true' => Helper::ABSOLUTE_WEIGHT_TRUE,
                            'false' => Helper::ABSOLUTE_WEIGHT_FALSE,
                        ],
                        'fit' => true,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Absolute Price field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getAbsolutePriceConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Absolute Price'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => Helper::KEY_ABSOLUTE_PRICE,
                        'dataType' => Number::NAME,
                        'prefer' => 'toggle',
                        'valueMap' => [
                            'true' => Helper::ABSOLUTE_PRICE_TRUE,
                            'false' => Helper::ABSOLUTE_PRICE_FALSE,
                        ],
                        'fit' => true,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    protected function getBaseCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    /**
     * Get weight unit name
     *
     * @return mixed
     */
    protected function getWeightUnit()
    {
        try {
            $unit = $this->locator->getStore()->getConfig('general/locale/weight_unit');
        } catch (\Exception $e) {
            $unit = $this->storeManager->getStore()->getConfig('general/locale/weight_unit');
        }

        return $unit;
    }

    /**
     * Check is current modifier for the product only
     *
     * @return bool
     */
    public function isProductScopeOnly()
    {
        return false;
    }
}
