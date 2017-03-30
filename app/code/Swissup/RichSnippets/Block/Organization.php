<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\RichSnippets\Block;

use Magento\Framework\View\Element\Template;

class Organization extends Template
{
    /**
     * @var string
     */
    protected $_template = 'organization.phtml';

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
        $organizatonParameters = array(
            'name'        => $this->_scopeConfig->getValue('richsnippets/organization/name'),
            'street'      => $this->_scopeConfig->getValue('richsnippets/organization/street'),
            'locality'    => $this->_scopeConfig->getValue('richsnippets/organization/locality'),
            'region'      => $this->_scopeConfig->getValue('richsnippets/organization/region'),          //deprecated
            'phone'       => $this->_scopeConfig->getValue('richsnippets/organization/phone'),
            'url'         => $this->_scopeConfig->getValue('richsnippets/organization/url'),
            'postalcode'  => $this->_scopeConfig->getValue('richsnippets/organization/postal_code'),
            'countryname' => $this->_scopeConfig->getValue('richsnippets/organization/country'),    //deprecated
            'email'       => $this->_scopeConfig->getValue('richsnippets/organization/email'),
            //Social Links
            'facebook'    => $this->_scopeConfig->getValue('richsnippets/social/facebook'),
            'twitter'     => $this->_scopeConfig->getValue('richsnippets/social/twitter'),
            'instagram'   => $this->_scopeConfig->getValue('richsnippets/social/instagram'),
            'pinterest'   => $this->_scopeConfig->getValue('richsnippets/social/pinterest'),          //deprecated
            'linkedin'    => $this->_scopeConfig->getValue('richsnippets/social/linkedin'),
            'googleplus'  => $this->_scopeConfig->getValue('richsnippets/social/googleplus')
        );

        if (array_filter($organizatonParameters)) {
            return $organizatonParameters;
        }

        return false;
    }
}
