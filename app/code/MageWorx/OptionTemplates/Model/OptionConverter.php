<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model;

class OptionConverter
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \MageWorx\OptionBase\Model\Option\Attributes
     */
    protected $optionAttributes;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MageWorx\OptionBase\Model\Option\Attributes $optionAttributes
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->optionAttributes = $optionAttributes;
    }

    /**
     * Convert product or group options to array and retrieve it.
     *
     * @param \Magento\Catalog\Model\Product | \MageWorx\OptionTemplates\Model\Group $object
     * @return array
     */
    public function getOptionsAsArray($object = null)
    {
        $optionsArr = $object->getOptions();

        if ($optionsArr == null) {
            $optionsArr = [];
        }

        $showPrice = true;
        $values = [];

        foreach ($optionsArr as $option) {
            /* @var $option \Magento\Catalog\Model\Product\Option */
            $value = [];
            $value['id'] = $option->getOptionId();
            $value['item_count'] = $object->getItemCount();
            $value['option_id'] = $option->getOptionId();
            $value['title'] = $option->getTitle();
            $value['type'] = $option->getType();
            $value['is_require'] = $option->getIsRequire();
            $value['sort_order'] = $option->getSortOrder();
            $value['can_edit_price'] = $object->getCanEditPrice();
            $value['group_option_id'] = $option->getGroupOptionId();

            if ($option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT &&
                $option->getValues()
            ) {
                $itemCount = 0;
                foreach ($option->getValues() as $_value) {
                    $i = $_value->getOptionTypeId();
                    /* @var $_value \Magento\Catalog\Model\Product\Option\Value */
                    $value['values'][$i] = [
                        'item_count' => max($itemCount, $_value->getOptionTypeId()),
                        'option_id' => $_value->getOptionId(),
                        'option_type_id' => $_value->getOptionTypeId(),
                        'title' => $_value->getTitle(),
                        'price' => $showPrice ?
                            $this->getPriceValue($_value->getPrice(), $_value->getPriceType()) :
                            0,
                        'price_type' => $showPrice && $_value->getPriceType() ?
                            $_value->getPriceType() :
                            'fixed',
                        'sku' => $_value->getSku(),
                        'sort_order' => $_value->getSortOrder(),
                        'group_option_value_id' => $_value->getGroupOptionValueId(),
                    ];
                }
            } else {
                $value['price'] = $showPrice ? $this->getPriceValue(
                    $option->getPrice(),
                    $option->getPriceType()
                ) : 0;
                $value['price_type'] = $option->getPriceType() ? $option->getPriceType() : 'fixed';
                $value['sku'] = $option->getSku();
                $value['max_characters'] = $option->getMaxCharacters();
                $value['file_extension'] = $option->getFileExtension();
                $value['image_size_x'] = $option->getImageSizeX();
                $value['image_size_y'] = $option->getImageSizeY();
                $value['values'] = null;
            }

            // Adds option attributes specified in the third-party module's to the option
            $value = $this->addSpecificOptionAttributes($value, $option);
            $values[$option->getOptionId()] = $value;
        }

        return $values;
    }

    /**
     * @param float $value
     * @param string $type
     * @return string
     */
    public function getPriceValue($value, $type)
    {
        if ($type == \Magento\Catalog\Model\Product\Option\Value::TYPE_PERCENT) {
            $result = number_format($value, 2, null, '');
        } else if ($type == 'fixed') {
            $result = number_format($value, 2, null, '');
        } else {
            $result = null;
        }

        /** Fixs incorrect result of the number_format function */
        if ($result === null) {
            $result = 0;
        }

        return $result;
    }

    /**
     * Add specific third-party modules option attributes
     *
     * @param $value
     * @param $option
     * @return array
     */
    protected function addSpecificOptionAttributes($value, $option)
    {
        $attributes = $this->optionAttributes->getData();
        foreach ($attributes as $attribute) {
            if ($option->getData($attribute) === null) {
                continue;
            }
            $value[$attribute] = $option->getData($attribute);
        }

        return $value;
    }
}
