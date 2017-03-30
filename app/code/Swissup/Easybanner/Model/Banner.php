<?php
namespace Swissup\Easybanner\Model;

use Swissup\Easybanner\Api\Data\BannerInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Banner extends \Magento\Rule\Model\AbstractModel
    implements BannerInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'banner_';

    /**
     * @var string
     */
    protected $_cacheTag = 'banner_';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'banner_';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Helper\Data $taxHelper,
        \Swissup\Easybanner\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookie,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_request = $request;
        $this->_session = $session;
        $this->_taxHelper = $taxHelper;
        $this->_localeDate = $localeDate;
        $this->_datetime = $datetime;
        $this->_coreRegistry = $registry;
        $this->_storeManager = $storeManager;
        $this->_combineFactory = $combineFactory;
        $this->_cookieManager  = $cookie;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\Easybanner\Model\ResourceModel\Banner');
    }

    /**
     * Prepare data before saving
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeSave()
    {
        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsConditions();
        }

        return $this;
    }

    public function loadByIdentifier($field, $value)
    {
        $this->load($value, $field);
        return $this;
    }

    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    public function getActionsInstance()
    {
        return $this;
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    public function getPlaceholderValuesForForm()
    {
        $options = [];
        $placeholders = $this->_getResource()->getPlaceholderValues();
        foreach ($placeholders as $placeholder) {
            $options[] = [
                'label' => $placeholder['name'],
                'value' => $placeholder['placeholder_id']
            ];
        }

        return $options;
    }

    public function getOptionBanners()
    {
        $sliders = $this->_getResource()->getOptionBanners();
        $options = [];
        foreach ($sliders as $item) {
            $options[] = ['value' => $item['identifier'], 'label' => $item['identifier']];
        }
        return $options;
    }

    /**
     * Checks all conditions of the banner
     *
     * @return bool
     */
    public function isVisible($storeId)
    {
        if (!$this->getStatus()
            || (!in_array($storeId, $this->getStores())
                && !in_array(0, $this->getStores()))) { // all stores

            return false;
        }

        $conditions = unserialize($this->getConditionsSerialized());
        return $this->_validateConditions($conditions);
    }

    protected function _validateConditions(&$filter, $aggregator = null, $value = null, $level = 0)
    {
        $result = true;
        $finalResult = null;
        if (isset($filter['aggregator']) && !empty($filter['conditions'])) {
            foreach ($filter['conditions'] as $key => $condition) {
                $result = $this->_validateConditions(
                    $condition,
                    $filter['aggregator'],
                    $filter['value'],
                    $level + 1
                );

                // unset false conditions to skip their validation on client side
                // @see js_conditions
                if (($filter['value'] == '1' && !$result)
                    || ($filter['value'] == '0' && $result)) {

                    unset($filter['conditions'][$key]);
                    $filter['conditions'] = array_values($filter['conditions']);
                }

                if (($filter['aggregator'] == 'all' && $filter['value'] == '1' && !$result)
                    || ($filter['aggregator'] == 'any' && $filter['value'] == '1' && $result)) {

                    if (null === $finalResult) {
                        $finalResult = $result;
                    }
                } elseif (($filter['aggregator'] == 'all' && $filter['value'] == '0' && $result)
                    || ($filter['aggregator'] == 'any' && $filter['value'] == '0' && !$result)) {

                    $result = !$result;
                    if (null === $finalResult) {
                        $finalResult = $result;
                    }
                }
            }
        } elseif (!empty($filter['attribute'])) {
            switch($filter['attribute']) {
                case 'category_ids':
                    if ($category = $this->_coreRegistry->registry('current_category')) {
                        $comparator = $category->getId();
                    } else {
                        $comparator = $this->_getRequestParam('category_id');
                    }
                    break;
                case 'product_ids':
                    if ($product = $this->_coreRegistry->registry('product')) {
                        $comparator = $product->getId();
                    } else {
                        $comparator = $this->_getRequestParam('product_id');
                    }
                    break;
                case 'time':
                    $filter['value'] = strtotime($filter['value']);
                    $comparator = strtotime($this->_datetime->gmtDate('H:i'));
                    break;
                case 'date':
                    $filter['value'] = strtotime($filter['value']);
                    $comparator = strtotime($this->_datetime->gmtDate('Y-m-d H:i:s'));
                    break;
                case 'handle':
                    $comparator = $this->_request->getFullActionName();
                    break;
                case 'clicks_count':
                    $comparator = $this->getClicksCount();
                    break;
                case 'display_count':
                    $comparator = $this->getDisplayCount();
                    break;
                case 'display_count_per_customer':
                    $_id = $this->getHtmlId($this->getIdentifier());
                    $comparator = $this->_cookieManager->getCookie($_id);
                    break;
                case 'customer_group':
                    $comparator = $this->_customerSession->getCustomerGroupId();
                    break;
                case 'subtotal_excl':
                    $comparator = $this->getSubtotal();
                    if (false === $comparator) {
                        return true;
                    }
                    break;
                case 'subtotal_incl':
                    $comparator = $this->getSubtotal(false);
                    if (false === $comparator) {
                        return true;
                    }
                    break;
                default:
                    // client side filters: activity|inactivity
                    // filters always has only 1 element, so we can return here
                    return true;
            }
            $result = $this->_compareCondition(
                $filter['value'], $comparator, $filter['operator']
            );
        }
        if (0 === $level) {
            $this->setJsConditions($filter);
        }

        if (null !== $finalResult) {
            return $finalResult;
        }
        return $result;
    }

    protected function _compareCondition($v1, $v2, $op)
    {
        if ($op=='()' || $op=='!()' || $op=='!=' || $op=='==' || $op=='{}' || $op=='!{}') {
            $v1 = explode(',', $v1);
            foreach ($v1 as &$v) {
                $v = trim($v);
            }
            if (!is_array($v2)) {
                $v2 = array($v2);
            }
        }

        $result = false;

        switch ($op) {
            case '==': case '!=':
                if (is_array($v1)) {
                    if (is_array($v2)) {
                        $result = array_diff($v2, $v1);
                        $result = empty($result) && (sizeof($v2) == sizeof($v1));
                    } else {
                        return false;
                    }
                } else {
                    if (is_array($v2)) {
                        $result = in_array($v1, $v2);
                    } else {
                        $result = $v2==$v1;
                    }
                }
                break;

            case '<=': case '>':
                if (is_array($v2)) {
                    $result = false;
                } else {
                    $result = $v2<=$v1;
                }
                break;

            case '>=': case '<':
                if (is_array($v2)) {
                    $result = false;
                } else {
                    $result = $v2>=$v1;
                }
                break;

            case '{}': case '!{}':
                if (is_array($v1)) {
                    if (is_array($v2)) {
                        $result = array_diff($v1, $v2);
                        $result = empty($result);
                    } else {
                        return false;
                    }
                } else {
                    if (is_array($v2)) {
                        $result = false;
                    } else {
                        $result = stripos((string)$v2, (string)$v1)!==false;
                    }
                }
                break;

            case '()': case '!()':
                if (is_array($v2)) {
                    $result = count(array_intersect($v2, (array)$v1)) > 0;
                } else {
                    $result = in_array($v2, (array)$v1);
                }
                break;
        }

        if ('!='==$op || '>'==$op || '<'==$op || '!{}'==$op || '!()'==$op) {
            $result = !$result;
        }

        return $result;
    }

    public function getHtmlId($identifier)
    {
        return 'banner-' . $this->cleanupName($identifier);
    }

    public function cleanupName($name)
    {
        return preg_replace('/[^a-z0-9_]+/i', '-', $name);
    }

    public function getSubtotal($skipTax = true)
    {
        $totals = $this->_session->getQuote()->getTotals();
        if (isset($totals['subtotal'])) {
            $config = $this->_taxHelper->getConfig();
            if ($config->displayCartSubtotalBoth()) {
                if ($skipTax) {
                    $subtotal = $totals['subtotal']->getValueExclTax();
                } else {
                    $subtotal = $totals['subtotal']->getValueInclTax();
                }
            } elseif ($config->displayCartSubtotalInclTax()) {
                $subtotal = $totals['subtotal']->getValueInclTax();
            } else {
                $subtotal = $totals['subtotal']->getValue();
                if (!$skipTax && isset($totals['tax'])) {
                    $subtotal+= $totals['tax']->getValue();
                }
            }

            return $subtotal;
        }
        return false;
    }

    protected function _getRequestParam($param)
    {
        $value = null;
        $request = $this->_request;
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        switch ($param) {
            case 'category_id':
                if ('catalog' === $module && 'view' === $action) {
                    if ('category' === $controller) {
                        $value = $request->getParam('id');
                    } elseif ('product' === $controller) {
                        $value = $request->getParam('category');
                    }
                }
                break;
            case 'product_id':
                if ('catalog' === $module
                    && 'product' === $controller
                    && 'view' === $action) {

                    $value = $request->getParam('id');
                }
                break;
        }
        return $value;
    }

    public function getClicksCount()
    {
        return $this->_getResource()->getClicksCount($this->getId());
    }

    public function getDisplayCount()
    {
        return $this->_getResource()->getDisplayCount($this->getId());
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get banner_id
     *
     * return int
     */
    public function getBannerId()
    {
        return $this->getData(self::BANNER_ID);
    }

    /**
     * Get identifier
     *
     * return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get sort_order
     *
     * return int
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Get title
     *
     * return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get url
     *
     * return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * Get image
     *
     * return string
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Get html
     *
     * return string
     */
    public function getHtml()
    {
        return $this->getData(self::HTML);
    }

    /**
     * Get status
     *
     * return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get mode
     *
     * return string
     */
    public function getMode()
    {
        return $this->getData(self::MODE);
    }

    /**
     * Get target
     *
     * return string
     */
    public function getTarget()
    {
        return $this->getData(self::TARGET);
    }

    /**
     * Get hide_url
     *
     * return int
     */
    public function getHideUrl()
    {
        return $this->getData(self::HIDE_URL);
    }

    /**
     * Get conditions_serialized
     *
     * return string
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * Get resize_image
     *
     * return int
     */
    public function getResizeImage()
    {
        return $this->getData(self::RESIZE_IMAGE);
    }

    /**
     * Get width
     *
     * return int
     */
    public function getWidth()
    {
        return $this->getData(self::WIDTH);
    }

    /**
     * Get height
     *
     * return int
     */
    public function getHeight()
    {
        return $this->getData(self::HEIGHT);
    }

    /**
     * Get retina_support
     *
     * return int
     */
    public function getRetinaSupport()
    {
        return $this->getData(self::RETINA_SUPPORT);
    }

    /**
     * Get background_color
     *
     * return string
     */
    public function getBackgroundColor()
    {
        return $this->getData(self::BACKGROUND_COLOR);
    }

    /**
     * Get class_name
     *
     * return string
     */
    public function getClassName()
    {
        return $this->getData(self::CLASS_NAME);
    }

    /**
     * Set banner_id
     *
     * @param int $bannerId
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setBannerId($bannerId)
    {
        return $this->setData(self::BANNER_ID, $bannerId);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set sort_order
     *
     * @param int $sortOrder
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set title
     *
     * @param string $title
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set url
     *
     * @param string $url
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * Set image
     *
     * @param string $image
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Set html
     *
     * @param string $html
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setHtml($html)
    {
        return $this->setData(self::HTML, $html);
    }

    /**
     * Set status
     *
     * @param int $status
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set mode
     *
     * @param string $mode
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setMode($mode)
    {
        return $this->setData(self::MODE, $mode);
    }

    /**
     * Set target
     *
     * @param string $target
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setTarget($target)
    {
        return $this->setData(self::TARGET, $target);
    }

    /**
     * Set hide_url
     *
     * @param int $hideUrl
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setHideUrl($hideUrl)
    {
        return $this->setData(self::HIDE_URL, $hideUrl);
    }

    /**
     * Set conditions_serialized
     *
     * @param string $conditionsSerialized
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * Set resize_image
     *
     * @param int $resizeImage
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setResizeImage($resizeImage)
    {
        return $this->setData(self::RESIZE_IMAGE, $resizeImage);
    }

    /**
     * Set width
     *
     * @param int $width
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setWidth($width)
    {
        return $this->setData(self::WIDTH, $width);
    }

    /**
     * Set height
     *
     * @param int $height
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setHeight($height)
    {
        return $this->setData(self::HEIGHT, $height);
    }

    /**
     * Set retina_support
     *
     * @param int $retinaSupport
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setRetinaSupport($retinaSupport)
    {
        return $this->setData(self::RETINA_SUPPORT, $retinaSupport);
    }

    /**
     * Set background_color
     *
     * @param string $backgroundColor
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setBackgroundColor($backgroundColor)
    {
        return $this->setData(self::BACKGROUND_COLOR, $backgroundColor);
    }

    /**
     * Set class_name
     *
     * @param string $className
     * return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setClassName($className)
    {
        return $this->setData(self::CLASS_NAME, $className);
    }
}
