<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Model;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface as OptionRepository;
use Magento\Framework\Webapi\Exception;

class OptionSaver
{
    const KEY_NEW_PRODUCT = 'new';

    const KEY_UPD_PRODUCT = 'upd';

    const KEY_DEL_PRODUCT = 'del';

    /**
     *
     * @var \MageWorx\OptionTemplates\Model\OptionConverter
     */
    protected $optionConverter;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $productOptionConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \MageWorx\OptionTemplates\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * Array contain all group option ids, that were added to personal product
     *
     * @var array
     */
    protected $productGroupNewOptionIds = [];

    /**
     * @var \MageWorx\OptionTemplates\Model\Group
     */
    protected $group;

    /**
     *
     * @var array
     */
    protected $deletedGroupOptions;

    /**
     *
     * @var array
     */
    protected $addedGroupOptions;

    /**
     *
     * @var array
     */
    protected $itersectedOptions;

    /**
     *
     * @var array
     */
    protected $products = [];

    /**
     * Array of modified options and modified/added option values
     *
     * @var array
     */
    protected $modifiedUpGroupOptions;

    /**
     * Array of deleted option values
     *
     * @var array
     */
    protected $modifiedDownGroupOptions;

    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory
     */
    protected $customOptionFactory;

    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array|null
     */
    protected $groupOptions;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \MageWorx\OptionTemplates\Model\Group\Source\SystemAttributes
     */
    protected $systemAttributes;

    /**
     *
     * @param \MageWorx\OptionTemplates\Model\OptionConverter $optionConverter
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @param \MageWorx\OptionTemplates\Model\GroupFactory $groupFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory
     * @param OptionRepository $optionRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \MageWorx\OptionTemplates\Model\OptionConverter $optionConverter,
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        \MageWorx\OptionTemplates\Model\GroupFactory $groupFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory,
        OptionRepository $optionRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger,
        \MageWorx\OptionTemplates\Model\Group\Source\SystemAttributes $systemAttributes
    ) {
        $this->optionConverter = $optionConverter;
        $this->productOptionConfig = $productOptionConfig;
        $this->groupFactory = $groupFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customOptionFactory = $customOptionFactory;
        $this->optionRepository = $optionRepository;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->systemAttributes = $systemAttributes;
    }

    /**
     *
     * @param Group $group The after save Group containing information on products
     * @param array $lastOptionAsArray Options data array from group before saving
     * @return void
     */
    public function saveProductOptions(Group $group, array $lastOptionAsArray)
    {
        $this->products[self::KEY_NEW_PRODUCT] = $group->getNewProductIds();
        $this->products[self::KEY_UPD_PRODUCT] = $group->getUpdProductIds();
        $this->products[self::KEY_DEL_PRODUCT] = $group->getDelProductIds();

        $allProductIds = $group->getAffectedProductIds();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addStoreFilter(0)
            ->setStoreId(0)
            ->addFieldToFilter('entity_id', ['in' => $allProductIds])
            ->addOptionsToResult(); // by default the product (collections item) has no options data

        /** Reload model for using new option ids **/
        /** @var Group group */
        $this->group = $this->groupFactory->create()->load($group->getId());
        $this->groupOptions = $this->optionConverter->getOptionsAsArray($this->group);

        $this->deletedGroupOptions = $this->getGroupDeletedOptions($lastOptionAsArray);
        $this->addedGroupOptions = $this->getGroupAddedOptions($lastOptionAsArray);
        $this->itersectedOptions = $this->getGroupIntersectedOptions($lastOptionAsArray);
        $groupNewModifiedOptions = $this->getGroupNewModifiedOptions($lastOptionAsArray);
        $groupLastModifiedOptions = $this->getGroupLastModifiedOptions($lastOptionAsArray);
        $this->modifiedUpGroupOptions = $this->arrayDiffRecursive($groupNewModifiedOptions, $groupLastModifiedOptions);
        $this->modifiedDownGroupOptions = $this->arrayDiffRecursive(
            $groupLastModifiedOptions,
            $groupNewModifiedOptions
        );

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $customOptions = [];
            $this->clearProductGroupNewOptionIds();
            $product->setStoreId(0);
            $preparedProductOptionArray = $this->getPreparedProductOptions($product);

            try {
                foreach ($preparedProductOptionArray as $preparedOption) {
                    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
                    if (is_object($preparedOption)) {
                        $customOption = $this->customOptionFactory->create(['data' => $preparedOption->getData()]);
                        $id = $preparedOption->getData('id');
                        $values = $preparedOption->getValues();
                    } else if (is_array($preparedOption)) {
                        $customOption = $this->customOptionFactory->create(['data' => $preparedOption]);
                        $id = $preparedOption['id'];
                        $values = !empty($preparedOption['values']) ? $preparedOption['values'] : [];
                    } else {
                        throw new Exception(__('The prepared option is not an instance of DataObject or array. %1 is received', gettype($preparedOption)));
                    }

                    $customOption->setProductSku($product->getSku())
                        ->setOptionId($id)
                        ->setValues($values);
                    $customOptions[] = $customOption;
                }
                if (!empty($customOptions)) {
                    $product->setOptions($customOptions);
                    $product->setCanSaveCustomOptions(true);
                    $this->saveOptionsInProduct($product);
                }

                $this->updateProductData($product);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }

            $this->doProductRelationAction($product->getId());
        }
    }

    /**
     * Transfer product based custom options attributes from group to the corresponding product
     *
     * @param \Magento\Catalog\Model\Product $entity
     */
    protected function updateProductData($entity)
    {
        $excludeAttributes = $this->systemAttributes->toArray();
        $groupData = $this->group->getData();
        foreach ($excludeAttributes as $attribute) {
            unset($groupData[$attribute]);
        }
        $entity->addData($groupData);
        $this->productRepository->save($entity);
    }

    /**
     * @param object $entity
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     */
    public function saveOptionsInProduct($entity)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($this->optionRepository->getProductOptions($entity) as $option) {
            $this->optionRepository->delete($option);
        }

        if ($entity->getOptions()) {
            foreach ($entity->getOptions() as $option) {
                $this->optionRepository->save($option);
            }
        }

        return $entity;
    }

    /**
     * @return void
     */
    protected function clearProductGroupNewOptionIds()
    {
        $this->productGroupNewOptionIds = [];
    }

    /**
     *
     * @param array $lastOptionAsArray
     * @return array
     */
    protected function getGroupDeletedOptions($lastOptionAsArray)
    {
        return array_diff_key($lastOptionAsArray, $this->groupOptions);
    }

    /**
     *
     * @param array $lastOptionAsArray
     * @return array
     */
    protected function getGroupAddedOptions($lastOptionAsArray)
    {
        return array_diff_key($this->groupOptions, $lastOptionAsArray);
    }

    /**
     *
     * @param array $lastOptionAsArray
     * @return array
     */
    protected function getGroupIntersectedOptions($lastOptionAsArray)
    {
        return array_intersect_key($this->groupOptions, $lastOptionAsArray);
    }

    /**
     *
     * @param array $lastOptionAsArray
     * @return array
     */
    protected function getGroupNewModifiedOptions($lastOptionAsArray)
    {
        $intersectedGroupOptionIds = array_keys($this->getGroupIntersectedOptions($lastOptionAsArray));
        $prepareNewGroupOptions = [];

        foreach ($intersectedGroupOptionIds as $optionId) {
            if (!empty($this->groupOptions[$optionId])) {
                $prepareNewGroupOptions[$optionId] = $this->groupOptions[$optionId];
            }
        }

        return $prepareNewGroupOptions;
    }

    /**
     *
     * @param array $lastOptionAsArray
     * @return array
     */
    protected function getGroupLastModifiedOptions($lastOptionAsArray)
    {
        $intersectedGroupOptionIds = array_keys($this->getGroupIntersectedOptions($lastOptionAsArray));
        $prepareLastGroupOptions = [];

        foreach ($intersectedGroupOptionIds as $optionId) {
            if (!empty($lastOptionAsArray[$optionId])) {
                $prepareLastGroupOptions[$optionId] = $lastOptionAsArray[$optionId];
            }
        }

        return $prepareLastGroupOptions;
    }

    /**
     * Retrieve new product options as array, that were built by group modification
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getPreparedProductOptions($product)
    {
        $productOptions = $this->optionConverter->getOptionsAsArray($product);

        if ($this->isNewProduct($product->getId())) {
            $productOptions = $this->addNewOptionProcess($productOptions);
        } else if ($this->isUpdProduct($product->getId())) {
            $productOptions = $this->deleteOptionProcess($productOptions);
            $productOptions = $this->addNewOptionProcess($productOptions);
            $productOptions = $this->modifyOptionProcess($productOptions);
        } else if ($this->isDelProduct($product->getId())) {
            $productOptions = $this->clearOptionProcess($productOptions);
        }

        return $productOptions;
    }

    /**
     * Delete options that were deleted in group
     *
     * @todo Delete All product option with group_option_id that missed in group.
     * @param array $productOptions
     * @param null $group
     * @return array
     */
    public function deleteOptionProcess(array $productOptions, $group = null)
    {
        if ($group === null) {
            $deletedGroupOptionIds = array_keys($this->deletedGroupOptions);
        } else {
            $groupOptions = $this->optionConverter->getOptionsAsArray($group);
            $deletedGroupOptionIds = array_keys($groupOptions);
        }

        foreach ($productOptions as $key => $productOption) {
            if (!empty($productOption['group_option_id']) &&
                in_array($productOption['group_option_id'], $deletedGroupOptionIds)
            ) {
                $productOption['is_delete'] = '1';
                $productOptions[$key] = $productOption;
            }
        }

        return $productOptions;
    }

    /**
     * Delete all group options
     *
     * @param array $productOptions
     * @return array
     */
    protected function clearOptionProcess(array $productOptions)
    {
        foreach ($productOptions as $key => $productOption) {
            if (!empty($productOption['group_option_id'])) {
                $productOption['is_delete'] = '1';
                $productOptions[$key] = $productOption;
            }
        }

        return $productOptions;
    }

    /**
     * Modify options that were modified in group
     *
     * @param array $productOptions
     * @return array
     */
    protected function modifyOptionProcess(array $productOptions)
    {
        foreach ($productOptions as $productOptionId => $productOption) {
            $groupOptionId = !empty($productOption['group_option_id']) ? $productOption['group_option_id'] : null;
            if (!$groupOptionId) {
                continue;
            }
            if ($this->isOptionWereRecreated($groupOptionId)) {
                continue;
            }
            if (!empty($this->modifiedDownGroupOptions[$groupOptionId])) {
                foreach ($this->modifiedDownGroupOptions[$groupOptionId] as $modPropertyKey => $modProperty) {
                    if ($modPropertyKey == 'values' && is_array($modProperty)) {
                        /**
                         * @todo is corresponding product option another type? we must recreate it early maybe.
                         */
                        if (empty($productOptions[$productOptionId][$modPropertyKey])) {
                            continue;
                        }

                        foreach ($modProperty as $valueId => $valueData) {
                            //Option value were deleted in group - delete it in corresponding product option
                            if (!empty($valueData['option_type_id'])) {
                                $productOptions[$productOptionId][$modPropertyKey] =
                                    $this->markProductOptionValueAsDelete(
                                        $productOptions[$productOptionId][$modPropertyKey],
                                        $valueData['option_type_id']
                                    );
                            } else {
                                $productOptions[$productOptionId][$modPropertyKey] =
                                    $this->getModifyProductOptionValue(
                                        $productOptions[$productOptionId][$modPropertyKey],
                                        $valueId,
                                        $valueData
                                    );
                            }
                        }
                    } else if (!is_array($modProperty)) {
                        unset($productOptions[$productOptionId][$modPropertyKey]);
                    }
                }
            }

            if (!empty($this->modifiedUpGroupOptions[$groupOptionId])) {
                foreach ($this->modifiedUpGroupOptions[$groupOptionId] as $modPropertyKey => $modProperty) {
                    if ($modPropertyKey == 'values' && is_array($modProperty)) {
                        /**
                         * @todo is corresponding product option another type? we must recreate it early maybe.
                         */
                        if (empty($productOptions[$productOptionId][$modPropertyKey])) {
                            continue;
                        }

                        foreach ($modProperty as $valueId => $valueData) {
                            if (!empty($valueData['option_type_id'])) {
                                $productOptions[$productOptionId][$modPropertyKey][] =
                                    $this->convertGroupOptionValueToProductOptionValue(
                                        $valueData,
                                        $productOptionId
                                    );
                            } else {
                                $productOptions[$productOptionId][$modPropertyKey] =
                                    $this->getModifyProductOptionValue(
                                        $productOptions[$productOptionId][$modPropertyKey],
                                        $valueId,
                                        $valueData
                                    );
                            }
                        }
                    } else if (!is_array($modProperty)) {
                        $productOptions[$productOptionId][$modPropertyKey] = $modProperty;
                    }
                }
            }
        }

        return $productOptions;
    }

    /**
     * Add new options that were added in group
     *
     * @param array $productOptions
     * @param Group|null
     * @return array
     */
    public function addNewOptionProcess(array $productOptions, $group = null)
    {
        if ($group === null) {
            $groupOptions = $this->groupOptions;
        } else {
            $groupOptions = $this->optionConverter->getOptionsAsArray($group);
        }

        $newProductOptions = [];

        $i = $productOptions ? max(array_keys($productOptions)) + 1 : 1;

        foreach ($groupOptions as $groupOption) {
            $issetGroupOptionInProduct = false;

            foreach ($productOptions as $productOption) {
                if (!empty($productOption['group_option_id'])
                    && $productOption['group_option_id'] == $groupOption['option_id']
                ) {
                    $issetGroupOptionInProduct = true;
                }
            }

            if (!$issetGroupOptionInProduct) {
                $groupOption['group_option_id'] = $groupOption['id'];
                $groupOption['id'] = (string)$i;
                $groupOption['option_id'] = '0';

                $groupOption = $this->convertGroupToProductOptionValues($groupOption);
                $newProductOptions[$i] = $groupOption;
                $this->productGroupNewOptionIds[] = $groupOption['group_option_id'];
            }
            $i++;
        }

        return $productOptions + $newProductOptions;
    }

    /**
     *
     * @param array $option
     * @return array
     */
    protected function convertGroupToProductOptionValues($option)
    {
        if (!empty($option['values'])) {
            foreach ($option['values'] as $valueKey => $value) {
                $value['group_option_value_id'] = $value['option_type_id'];
                $value['option_type_id'] = '-1';
                $option['values'][$valueKey] = $value;
            }
        }

        return $option;
    }

    /**
     *
     * @param int $productId
     */
    protected function doProductRelationAction($productId)
    {
        if ($this->isNewProduct($productId)) {
            $this->group->addProductRelation($productId);
        } else if ($this->isDelProduct($productId)) {
            $this->group->deleteProductRelation($productId);
        }
    }

    /**
     *
     * @param int $productId
     * @return boolean
     */
    protected function isNewProduct($productId)
    {
        return in_array($productId, $this->products[self::KEY_NEW_PRODUCT]);
    }

    /**
     *
     * @param int $productId
     * @return boolean
     */
    protected function isUpdProduct($productId)
    {
        return in_array($productId, $this->products[self::KEY_UPD_PRODUCT]);
    }

    /**
     *
     * @param int $productId
     * @return boolean
     */
    protected function isDelProduct($productId)
    {
        return in_array($productId, $this->products[self::KEY_DEL_PRODUCT]);
    }

    /**
     * Check if different options types
     *
     * @param string $typeOld
     * @param string $typeNew
     * @return bool
     */
    protected function isSameOptionGroupType($typeOld, $typeNew)
    {
        return ($this->getOptionGroupType($typeOld) == $this->getOptionGroupType($typeNew));
    }

    /**
     *
     * @param string $name
     * @return string
     */
    protected function getOptionGroupType($name)
    {
        foreach ($this->productOptionConfig->getAll() as $typeName => $data) {
            if (!empty($data['types'][$name])) {
                return $typeName;
            }
        }

        return null;
    }

    /**
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    protected function arrayDiffRecursive($arr1, $arr2)
    {
        $outputDiff = [];

        foreach ($arr1 as $key => $value) {
            if (array_key_exists($key, $arr2)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->arrayDiffRecursive($value, $arr2[$key]);
                    if (count($recursiveDiff)) {
                        $outputDiff[$key] = $recursiveDiff;
                    }
                } else if ($arr2[$key] != $value) {
                    $outputDiff[$key] = $value;
                }
            } else {
                $outputDiff[$key] = $value;
            }
        }

        return $outputDiff;
    }

    /**
     * Check if option was recreated
     *
     * @param string $groupOptionId
     * @return bool
     */
    protected function isOptionWereRecreated($groupOptionId)
    {
        return in_array($groupOptionId, $this->productGroupNewOptionIds);
    }

    /**
     * Simple convert group option value to product option value
     *
     * @param array $groupOptionValueData
     * @param int $productOptionId
     * @return string
     */
    protected function convertGroupOptionValueToProductOptionValue(array $groupOptionValueData, $productOptionId)
    {
        $groupOptionValueData['option_id'] = (string)$productOptionId;
        $groupOptionValueData['group_option_value_id'] = $groupOptionValueData['option_type_id'];
        $groupOptionValueData['option_type_id'] = '-1';

        return $groupOptionValueData;
    }

    /**
     * Mark 'delete' a product option value by deleted group option value
     *
     * @param array $productOptionValueArray
     * @param int $groupOptionValueId
     * @return array
     */
    protected function markProductOptionValueAsDelete(array $productOptionValueArray, $groupOptionValueId)
    {
        foreach ($productOptionValueArray as $optionValueKey => $optionData) {
            if (!empty($optionData['group_option_value_id']) &&
                $groupOptionValueId == $optionData['group_option_value_id']
            ) {
                $productOptionValueArray[$optionValueKey]['is_delete'] = '1';
                break;
            }
        }

        return $productOptionValueArray;
    }

    /**
     * Modify/add product option value properties by modified group option value properties
     *
     *
     * @param array $productOptionValueArray
     * @param int $groupOptionValueId
     * @param array $valueData
     * @return array
     */
    protected function getModifyProductOptionValue(array $productOptionValueArray, $groupOptionValueId, $valueData)
    {
        foreach ($productOptionValueArray as $optionValueKey => $optionValue) {
            if (!empty($optionValue['group_option_value_id']) &&
                $groupOptionValueId == $optionValue['group_option_value_id']
            ) {
                foreach ($valueData as $key => $value) {
                    $productOptionValueArray[$optionValueKey][$key] = $value;
                }
                break;
            }
        }

        return $productOptionValueArray;
    }
}
