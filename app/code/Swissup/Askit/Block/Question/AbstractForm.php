<?php

namespace Swissup\Askit\Block\Question;

use Swissup\Askit\Block\Question\AbstractBlock;
use Swissup\Askit\Api\Data\ItemInterface;

class AbstractForm extends AbstractBlock
{
    protected $formId;

    /**
     * @var \Swissup\Askit\Helper\Form
     */
    protected $formHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Swissup\Askit\Helper\Config $configHelper
     * @param \Swissup\Askit\Helper\Url $urlHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Swissup\Askit\Model\Vote\Factory $voteFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Swissup\Askit\Helper\Config $configHelper,
        \Swissup\Askit\Helper\Url $urlHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Swissup\Askit\Model\Vote\Factory $voteFactory,
        \Swissup\Askit\Helper\Form $formHelper,
        array $data = []
    ) {
        $this->formHelper = $formHelper
            ->setFormId($this->formId);

        parent::__construct(
            $context,
            $customerSession,
            $configHelper,
            $urlHelper,
            $postDataHelper,
            $voteFactory,
            $data
        );
    }

    /**
     *
     * @return \Swissup\Askit\Helper\Form
     */
    public function getFormHelper()
    {
        return $this->formHelper;
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'captcha',
            $this->getLayout()->createBlock('Magento\Captcha\Block\Captcha')
                ->setFormId($this->formId)
                ->setImgWidth(230)
                ->setImgHeight(50)
        );
        return parent::_prepareLayout();
    }
}
