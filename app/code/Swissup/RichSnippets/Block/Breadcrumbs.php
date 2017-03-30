<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\RichSnippets\Block;

use Magento\Framework\View\Element\Template;

class Breadcrumbs extends Template
{
    /**
     * @var string
     */
    protected $_template = 'breadcrumbs.phtml';

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;

        parent::__construct($context, $data);
    }

    public function canShowContent()
    {
        if (!$this->_scopeConfig->getValue('richsnippets/breadcrumbs/enabled')) {
            return false;
        }
        $breadcrumbs = $this->_objectManager->get('Swissup\RichSnippets\Plugin\Breadcrumbs');
        if ($breadcrumbs->crumbs) {
            return $breadcrumbs->crumbs;
        }

        return false;
    }
}
