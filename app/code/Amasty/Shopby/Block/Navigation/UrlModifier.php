<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Block\Navigation;


class UrlModifier extends \Magento\Framework\View\Element\Template
{
    const VAR_REPLACE_URL = 'amasty_shopby_replace_url';

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Amasty_Shopby::navigation/url_modifier.phtml';

    public function getCurrentUrl()
    {
        return $this->getUrl('*/*/*', [
            '_current' => true,
            '_use_rewrite' => true
        ]);
    }

    public function replaceUrl()
    {
        return $this->getRequest()->getParam(\Amasty\Shopby\Block\Navigation\UrlModifier::VAR_REPLACE_URL) !== null;
    }
}