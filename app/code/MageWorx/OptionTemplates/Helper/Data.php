<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**#@+
     * Admin config settings
     */
    const XML_PATH_HIDE_ALL_OPTIONS = 'mageworx_optiontemplates/main/hide_all_options';

    /**
     * Check if hide all product options related to option groups in the frontend
     *
     * @param int $storeId
     * @return bool
     */
    public function isHideAllOptions($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HIDE_ALL_OPTIONS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
