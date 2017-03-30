<?php
namespace Swissup\ProLabels\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Context;
use Magento\Store\Model\ScopeInterface;

class Labels extends Template
{
    /**
     * @var \Swissup\ProLabels\Helper\ProductLabels
     */
    public $systemLabels;
    /**
     * @var \Swissup\ProLabels\Helper\ProductLabels
     */
    public $labelModel;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\DataObject $labelsData,
     * @param \Magento\Framework\Registry $registry,
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Swissup\ProLabels\Helper\ProductLabels $systemLabels,
        \Swissup\ProLabels\Model\Label $labelModel,
        array $data = []
    ) {
        $this->_scopeConfig   = $context->getScopeConfig();
        $this->_systemLabels = $systemLabels;
        $this->_coreRegistry = $registry;
        $this->_labelModel = $labelModel;
        $this->_storeManager = $context->getStoreManager();
        $this->_labels = [];
        $this->_product = $this->_coreRegistry->registry('product');
        $this->_initDefaultLabels();
        $this->_initManualLabels();

        parent::__construct($context, $data);
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData(
            [
                'cache_lifetime' => 86400,
                'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG]
            ]
        );
    }
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $httpContext = $_objectManager->get('Magento\Framework\App\Http\Context');
        $product = $this->_coreRegistry->registry('product');
        $labelIds = $this->_labelModel->getProductLabels($this->_product->getId());
        return [
            'PROLABELS_LABELS',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $httpContext->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            implode(",", $labelIds),
            $product->getId()
        ];
    }

    protected function _initDefaultLabels()
    {
        if ($onSale = $this->_systemLabels->getOnSaleLabel($this->_product, "product")) {
            $this->_labels[$onSale->getPosition()][] = $onSale;
        }
        if ($isNew = $this->_systemLabels->getIsNewLabel($this->_product, "product")) {
            $this->_labels[$isNew->getPosition()][] = $isNew;
        }
        if ($inStock = $this->_systemLabels->getStockLabel($this->_product, "product")) {
            $this->_labels[$inStock->getPosition()][] = $inStock;
        }
        if ($outOfStock = $this->_systemLabels->getOutOfStockLabel($this->_product, "product")) {
            $this->_labels[$outOfStock->getPosition()][] = $outOfStock;
        }
    }

    protected function _initManualLabels()
    {
        $labelIds = $this->_labelModel->getProductLabels($this->_product->getId());
        if (count($labelIds) == 0) {
            return false;
        }
        $collection = $this->_labelModel->getCollection();
        $collection->addFieldToFilter('label_id', $labelIds);
        $collection->addFieldToFilter('status', 1);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $customerGroupId = $customerSession->getCustomerGroupId();
        $storeId = $this->_storeManager->getStore()->getId();
        foreach ($collection as $label) {
            $labelStores = $label['store_id'];
            if (!in_array('0', $labelStores)) {
                if (!in_array($storeId, $labelStores)) { continue; }
            }
            $labelGroupIds = unserialize($label->getCustomerGroups());
            if (!in_array($customerGroupId, $labelGroupIds)) {
                continue;
            }
            $labelConfig = [
                'position' => $label->getProductPosition(),
                'text' => $label->getProductText(),
                'custom' => $label->getProductCustomStyle(),
                'custom_url' => $label->getProductCustomUrl(),
                'round_method' => $label->getProductRoundMethod(),
                'round_value' => $label->getProductRoundValue(),
                'image' => $label->getProductImage()
            ];
            $labelData = $this->_systemLabels->getLabelOutputObject($labelConfig, $this->_product, "product");
            $this->_labels[$labelData->getPosition()][] = $labelData;
        }
    }

    public function getLabels()
    {
        return $this->_labels;
    }

    public function getLabelImage($configImage)
    {
        return $this->_systemLabels->getUploadedLabelImage($configImage, "product");
    }

    public function getLabelImagePath($configImage)
    {
        return $this->_systemLabels->getUploadedLabelImagePath($configImage, "product");
    }

    public function getBaseImageWrapConfig()
    {
        return $this->_scopeConfig->getValue("prolabels/general/base", ScopeInterface::SCOPE_STORE);
    }

    public function getContentWrapConfig()
    {
        return $this->_scopeConfig->getValue("prolabels/general/content", ScopeInterface::SCOPE_STORE);
    }
}
