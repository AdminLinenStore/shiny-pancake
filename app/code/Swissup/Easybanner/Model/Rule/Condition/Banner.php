<?php
namespace Swissup\Easybanner\Model\Rule\Condition;

/**
 * Class Banner
 */
class Banner extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
    public function loadAttributeOptions()
    {
        $attributes = [
            'category_ids'      => __('Category'),
            'product_ids'       => __('Product'),
            'subtotal_excl'     => __('Subtotal (Excl.Tax)'),
            'subtotal_incl'     => __('Subtotal (Incl.Tax)'),
            'date'              => __('Date'),
            'time'              => __('Time'),
            'handle'            => __('Page'),
            'clicks_count'      => __('Clicks Count'),
            'display_count'     => __('Display Count'),
            'customer_group'    => __('Customer Group'),
            'display_count_per_customer' => __('Display Count per Customer'),
            'browsing_time'     => __('Lightbox/Awesomebar: Customer browsing time (seconds)'),
            'inactivity_time'   => __('Lightbox/Awesomebar: Customer inactivity time (seconds)'),
            'activity_time'     => __('Lightbox/Awesomebar: Customer activity time (seconds)'),
            'scroll_offset'     => __('Lightbox/Awesomebar: Scroll offset')
        ];
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getValue()
    {
        if ($this->getInputType() == 'time') {
            if (null === $this->getData('value')) {
                $this->setValue('00:00');
            }
        }

        return parent::getValue();
    }

    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'product_ids':
            case 'category_ids':
            case 'customer_group':
            case 'handle':
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __(
                    'Open Chooser'
                ) . '" /></a>';
        }
        return $html;
    }

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'product_ids':
                $url = '*/banner_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;

            case 'category_ids':
                $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;

            case 'customer_group':
                $url = '*/banner_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;

            case 'handle':
                $url = '*/banner_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;

            default:
                break;
        }

        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }

    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case 'product_ids': case 'category_ids': case 'customer_group': case 'handle':
                return true;
            default:
                break;
        }
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
                default:
                    break;
            }
        }
        return false;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'category_ids': case 'product_ids':
            case 'customer_group': case 'handle':
                return 'grid';
            case 'date':
                return 'date';
            case 'time':
                return 'time';
            case 'inactivity_time':
            case 'activity_time':
            case 'browsing_time':
            case 'scroll_offset':
            case 'subtotal_excl':
            case 'subtotal_incl':
                return 'interval';
            case 'display_count':
            case 'clicks_count':
            case 'display_count_per_customer':
                return 'increment';
            default:
                return 'string';
        }
    }
    /**
     * Add increment, time operators
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=='  => __('is'),
            '!='  => __('is not'),
            '>='  => __('equals or greater than'),
            '<='  => __('equals or less than'),
            '>'   => __('greater than'),
            '<'   => __('less than'),
            '{}'  => __('contains'),
            '!{}' => __('does not contain'),
            '()'  => __('is one of'),
            '!()' => __('is not one of'),
        ));
        $this->setOperatorByInputType(array(
            'string' => array('==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'),
            'numeric' => array('==', '!=', '>=', '>', '<=', '<', '()', '!()'),
            'increment' => array('<'),
            'interval' =>  array('<', '>'),
            'time' => array('==', '>=', '<='),
            'date' => array('==', '>=', '<='),
            'select' => array('==', '!='),
            'multiselect' => array('==', '!=', '{}', '!{}'),
            'grid' => array('()', '!()'),
        ));

        return $this;
    }
}
