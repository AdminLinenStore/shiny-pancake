<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\Easybanner\Block;

use Magento\Framework\View\Element\Template;

class Popup extends Template
{
    /**
     * @var string
     */
    protected $_template = 'popup.phtml';

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Swissup\Easybanner\Model\Banner $bannerModel
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory $bannerCollection,
        \Magento\Framework\ObjectManagerInterface $_objectManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_bannerCollection = $bannerCollection;
        $this->_objectManager = $_objectManager;
        $this->_jsonEncoder = $jsonEncoder;

        parent::__construct($context, $data);
    }

    public function getBanners()
    {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $_bannerCollection = $this->_bannerCollection->create();

        $_bannerCollection->getSelect()
            ->where('type in (?)', [2, 3])
            ->where('status = ?', 1);

        return $_bannerCollection->load();
    }

    public function getJsonConditions($conditions)
    {
        return $this->_jsonEncoder->encode($conditions);
    }

    public function getHtmlId($identifier)
    {
        return 'banner-' . $this->cleanupName($identifier);
    }

    public function cleanupName($name)
    {
        return preg_replace('/[^a-z0-9_]+/i', '-', $name);
    }

    public function getClassName($identifier, $mode)
    {
        $name = 'banner-' . $this->cleanupName($identifier);
        return 'placeholder-' . $mode . ' ' . $name;
    }
}
