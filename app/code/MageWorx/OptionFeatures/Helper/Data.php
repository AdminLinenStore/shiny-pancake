<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    // Option value attributes
    const KEY_IS_DEFAULT = 'is_default';
    const KEY_COST = 'cost';
    const KEY_WEIGHT = 'weight';

    // Option attributes
    const KEY_QTY_INPUT = 'qty_input';
    const KEY_ONE_TIME = 'one_time';

    // Product attributes
    const KEY_ABSOLUTE_COST = 'absolute_cost';
    const KEY_ABSOLUTE_WEIGHT = 'absolute_weight';
    const KEY_ABSOLUTE_PRICE = 'absolute_price';

    // Value map
    const IS_DEFAULT_TRUE = '1';
    const IS_DEFAULT_FALSE = '0';
    const QTY_INPUT_TRUE = '1';
    const QTY_INPUT_FALSE = '0';
    const ONE_TIME_TRUE = '1';
    const ONE_TIME_FALSE = '0';
    const ABSOLUTE_COST_TRUE = '1';
    const ABSOLUTE_COST_FALSE = '0';
    const ABSOLUTE_WEIGHT_TRUE = '1';
    const ABSOLUTE_WEIGHT_FALSE = '0';
    const ABSOLUTE_PRICE_TRUE = '1';
    const ABSOLUTE_PRICE_FALSE = '0';

    // Config
    const XML_PATH_USE_WEIGHT = 'mageworx_optionfeatures/main/use_weight';
    const XML_PATH_USE_COST = 'mageworx_optionfeatures/main/use_cost';
    const XML_PATH_USE_ABSOLUTE_COST = 'mageworx_optionfeatures/main/use_absolute_cost';
    const XML_PATH_USE_ABSOLUTE_WEIGHT = 'mageworx_optionfeatures/main/use_absolute_weight';
    const XML_PATH_USE_ABSOLUTE_PRICE = 'mageworx_optionfeatures/main/use_absolute_price';
    const XML_PATH_USE_ONE_TIME = 'mageworx_optionfeatures/main/use_one_time';

    /**
     * Check if 'use weight' are enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isWeightEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_WEIGHT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'use absolute weight' are enable
     * Depends on the 'use weight' flag
     *
     * @param int $storeId
     * @return bool
     */
    public function isAbsoluteWeightEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_ABSOLUTE_WEIGHT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) && $this->isWeightEnabled($storeId);
    }

    /**
     * Check if 'use cost' are enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isCostEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_COST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'use absolute cost' are enable
     * Depends on the 'use cost' flag
     *
     * @param int $storeId
     * @return bool
     */
    public function isAbsoluteCostEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_ABSOLUTE_COST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) && $this->isCostEnabled($storeId);
    }

    /**
     * Check if 'use absolute price' are enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isAbsolutePriceEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_ABSOLUTE_PRICE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'one time' are enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isOneTimeEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_ONE_TIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
