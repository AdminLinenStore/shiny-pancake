<?php
namespace Swissup\Easybanner\Model\Rule\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Swissup\Easybanner\Model\Rule\Condition\BannerFactory
     */
    protected $_bannerFactory;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Swissup\Easybanner\Model\Rule\Condition\BannerFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Swissup\Easybanner\Model\Rule\Condition\BannerFactory $conditionFactory,
        array $data = []
    ) {
        $this->_bannerFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType('Swissup\Easybanner\Model\Rule\Condition\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $bannerAttributes = $this->_bannerFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($bannerAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Swissup\Easybanner\Model\Rule\Condition\Banner|' . $code,
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Swissup\Easybanner\Model\Rule\Condition\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Conditions'), 'value' => $attributes]
            ]
        );
        return $conditions;
    }

    /**
     * @param array $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($bannerCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Banner|Combine $condition */
            $condition->collectValidatedAttributes($bannerCollection);
        }
        return $this;
    }
}
