<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\RichSnippets\Block;

use Magento\Framework\View\Element\Template;

class Website extends Template
{
    /**
     * @var string
     */
    protected $_template = 'website.phtml';

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    public function canShowContent()
    {
        $websiteParameters = array(
            'sitename' => $this->_scopeConfig->getValue('richsnippets/website/sitename'),
            'siteurl' => $this->_scopeConfig->getValue('richsnippets/website/siteurl')
        );

        if (array_filter($websiteParameters)) {
            return $websiteParameters;
        }

        return false;
    }
}
