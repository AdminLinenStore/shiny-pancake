<?php

namespace Swissup\Ajaxsearch\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_FOLDED_ENABLED = 'ajaxsearch/folded/enable';

    /**
     * @var string
     */
    const CONFIG_PATH_FOLDED_EFFECT = 'ajaxsearch/folded/effect';

    /**
     * Retrieve folded design flag
     *
     * @return boolean
     */
    public function isFoldedDesignEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_FOLDED_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Folded Effect
     *
     * @return string
     */
    public function getFoldedEffect()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_FOLDED_EFFECT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve ajaxsearch block additional css classes
     *
     * @return boolean
     */
    public function getBlockCssClass()
    {
        $classes = [];
        if ($this->isFoldedDesignEnabled()) {
            $classes[] = 'folded';
            $classes[] = $this->getFoldedEffect();
        }
        return implode(' ', $classes);
    }
}
