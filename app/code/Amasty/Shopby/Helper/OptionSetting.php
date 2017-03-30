<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Helper;

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\App\Helper\Context;
use Amasty\Shopby;
use Amasty\Shopby\Model\ResourceModel\OptionSetting\Collection;
use Amasty\Shopby\Model\ResourceModel\OptionSetting\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class OptionSetting extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var  Collection */
    protected $collection;

    /** @var  Shopby\Model\OptionSettingFactory */
    protected $settingFactory;

    /** @var  Repository */
    protected $_repository;

    /** @var ScopeConfigInterface */
    protected $_scopeConfig;

    public function __construct(
        Context $context,
        CollectionFactory $optionCollectionFactory,
        Shopby\Model\OptionSettingFactory $settingFactory,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->collection = $optionCollectionFactory->create();
        $this->settingFactory = $settingFactory;
        $this->_repository = $repository;
        $this->_scopeConfig = $context->getScopeConfig();
    }

    /**
     * @param string $value
     * @param string $filterCode
     * @param int $storeId
     * @return Shopby\Api\Data\OptionSettingInterface
     */
    public function getSettingByValue($value, $filterCode, $storeId)
    {
        /** @var Shopby\Model\OptionSetting $setting */
        $setting = $this->settingFactory->create();
        $setting = $setting->getByParams($filterCode, $value, $storeId);

        if (!$setting->getId()) {
            $setting->setFilterCode($filterCode);
            $attributeCode = substr($filterCode, 5);
            $attribute = $this->_repository->get($attributeCode);
            foreach ($attribute->getOptions() as $option)
            {
                if ($option->getValue() == $value) {
                    $this->initiateSettingByOption($setting, $option);
                    break;
                }
            }
        }

        return $setting;
    }

    protected function initiateSettingByOption(Shopby\Api\Data\OptionSettingInterface $setting, AttributeOptionInterface $option)
    {
        $setting->setValue($option->getValue());
        $setting->setTitle($option->getLabel());
        $setting->setMetaTitle($option->getLabel());
        return $this;
    }

    /**
     * update option Setting Collection on brand attribute change
     */
    public function updateOptionSettings()
    {
        $attrCode   = $this->_scopeConfig->getValue('amshopby_brand/general/attribute_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $filterCode = FilterSetting::ATTR_PREFIX . $attrCode;
        if (!$attrCode) {
            return;
        }
        $options = $this->_repository->get($attrCode)->getOptions();
        $optionValues = [];
        foreach ($options as $option) {
            if (!$option->getValue()) {
                continue;
            }
            $optionValues[] = $option->getValue();
        }
        // delete
        /** @var Shopby\Model\OptionSetting $optionSetting */
        $optionSetting = $this->settingFactory->create();
        $settingCollection = $optionSetting->getCollection();
        $optionsToDelete = $settingCollection->addFieldToFilter('value', ['nin' => $optionValues]);
        foreach ($optionsToDelete as $optionSetting ) {
            $optionSetting->delete();   //@todo deprecated
        }
        //add
        foreach ($optionValues as $value) {
            $setting = $this->getSettingByValue($value, $filterCode, 0);
            if (!$setting->getId()) {
                $setting->save();       //@todo deprecated
            }
        }
    }
}
