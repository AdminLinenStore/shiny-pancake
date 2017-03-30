<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\Easybanner\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Banner extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'banner.phtml';

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Swissup\Easybanner\Model\Banner $bannerModel
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Swissup\Easybanner\Model\Banner $bannerModel,
        \Swissup\Easybanner\Helper\Image $imageHelper,
        \Magento\Framework\ObjectManagerInterface $_objectManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookie,
        array $data = []
    ) {
        $this->_bannerModel = $bannerModel;
        $this->_imageHelper = $imageHelper;
        $this->_objectManager = $_objectManager;
        $this->_storeManager  = $context->getStoreManager();
        $this->_cookieManager  = $cookie;

        parent::__construct($context, $data);
    }

    public function getBannerData()
    {
        if ($this->getBannerObject()) {
            $this->_bannerModel = $this->getBannerObject();
        } else {
            $bannerId = $this->getBanner();
            if (!$bannerId) {
                return false;
            }
            $this->_bannerModel->load($bannerId);
        }

        if (!$this->_bannerModel->getId()
                || !$this->_bannerModel->getStatus()) {
            return false;
        }

        if (!$this->getPopupMode()) {
            if ((int)$this->_bannerModel->getType() !== 1) {
                return false;
            }
        }
        $storeId = $this->_storeManager->getStore()->getId();

        if (!$this->_bannerModel->isVisible($storeId)) {
            return false;
        }

        $statistic = $this->_objectManager->create('Swissup\Easybanner\Model\BannerStatistic');
        $statistic->incrementDisplayCount($this->_bannerModel->getId());

        $_id = $this->getHtmlId($this->_bannerModel->getIdentifier());

        if ($showCount = $this->_cookieManager->getCookie($_id)) {
            $increment = $showCount + 1;
            $this->_cookieManager->setPublicCookie($_id, $increment);
        } else {
            $this->_cookieManager->setPublicCookie($_id, 1);
        }

        return $this->_bannerModel;
    }

    public function getBannerUrl()
    {
        $url = 'easybanner/click/index/id/' . $this->_bannerModel->getId();
        if (!$this->_bannerModel->getHideUrl()) {
            $url .= '/url/' . trim($this->_bannerModel->getUrl(), '/');
        }

        return $url;
    }

    public function getBannerImage()
    {
        if (!$image = $this->_bannerModel->getImage()) {
            return false;
        }

        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . 'easybanner' . $image;
    }

    public function resizeImage($width, $height)
    {
        return $this->_imageHelper->resize($this->_bannerModel, $width, $height);
    }

    public function getBannerHtml()
    {
        $bannerHtml = $this->_bannerModel->getHtml();
        $bannerHtml = str_replace('{{tm_banner_url}}', $this->getUrl($this->getBannerUrl()), $bannerHtml);
        $cmsFilter = $this->_objectManager->get('Magento\Cms\Model\Template\FilterProvider');
        $storeId = $this->_storeManager->getStore()->getId();
        $html = $cmsFilter->getBlockFilter()
            ->setStoreId($storeId)
            ->filter($bannerHtml);

        return $html;
    }

    public function getJsConditions()
    {
        return $this->_bannerModel->getJsConditions();
    }

    public function getHtmlId($identifier)
    {
        return 'banner-' . $this->cleanupName($identifier);
    }

    public function getBannerClassName()
    {
        $class = $this->getHtmlId($this->_bannerModel->getIdentifier());
        if ($this->_bannerModel->getClassName()) {
            $class .= ' ' . $this->_bannerModel->getClassName();
        }
        if ($this->_bannerModel->getAdditionalCssClass()) {
            $class .= ' ' . $this->_bannerModel->getAdditionalCssClass();
        }
        return $class;
    }

    public function cleanupName($name)
    {
        return preg_replace('/[^a-z0-9_]+/i', '-', $name);
    }
}
