<?php
namespace Swissup\Easybanner\Block\Adminhtml\Banner\Edit\Tab;

use Magento\Framework\UrlInterface;
use Magento\Cms\Model\Wysiwyg\Config;

class Content extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Swissup\Askit\Helper\Url $urlHelper
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->_wysiwygConfig = $wysiwygConfig;

        parent::__construct($context, $registry, $formFactory, $data);
    }

   /**
    *
    * @return \Swissup\Easybanner\Model\Label
    */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('easybanner_banner');
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Swissup\Easybanner\Model\Label */
        $model = $this->_getModel();

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Swissup_Easybanner::banner_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('banner_');

        $generalFieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        $generalFieldset->addField(
            'url',
            'text',
            [
                'name' => 'url',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $generalFieldset->addField(
            'class_name',
            'text',
            [
                'name' => 'class_name',
                'label' => __('Css Class'),
                'title' => __('Css Class'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $mode = $generalFieldset->addField(
            'mode',
            'select',
            [
                'label' => __('Mode'),
                'title' => __('Mode'),
                'name' => 'mode',
                'required' => true,
                'options' => [
                    'image' => __('Image'),
                    'html' => __('Html')
                ],
                'disabled' => $isElementDisabled
            ]
        );

        $imageFieldset = $form->addFieldset(
            'image_fieldset',
            ['legend' => __('Image Options'), 'class' => 'fieldset-wide']
        );

        $imageFieldset->addType('image', 'Swissup\Easybanner\Block\Adminhtml\Banner\Helper\Image');

        $title = $imageFieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $image = $imageFieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $width = $imageFieldset->addField(
            'width',
            'text',
            [
                'name' => 'width',
                'label' => __('Width'),
                'title' => __('Width'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $height = $imageFieldset->addField(
            'height',
            'text',
            [
                'name' => 'height',
                'label' => __('Height'),
                'title' => __('Height'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $resize = $imageFieldset->addField(
            'resize_image',
            'select',
            [
                'label' => __('Use image resizer'),
                'title' => __('Use image resizer'),
                'name' => 'resize_image',
                'required' => false,
                'options' => [
                    '0' => __('No'),
                    '1' => __('Yes')
                ],
                'disabled' => $isElementDisabled
            ]
        );

        $retina = $imageFieldset->addField(
            'retina_support',
            'select',
            [
                'label' => __('Retina support'),
                'title' => __('Retina support'),
                'name' => 'retina_support',
                'required' => false,
                'options' => [
                    '0' => __('No'),
                    '1' => __('Yes')
                ],
                'note' => __('Actual image size should be twice larger then entered width and height'),
                'disabled' => $isElementDisabled
            ]
        );

        $background = $imageFieldset->addField(
            'background_color',
            'text',
            [
                'name' => 'background_color',
                'label' => __('Background Color'),
                'title' => __('Background Color'),
                'required' => false,
                'note' => __('255,255,255'),
                'disabled' => $isElementDisabled
            ]
        );

        $htmlFieldset = $form->addFieldset(
            'html_fieldset',
            ['legend' => __('Html Content'), 'class' => 'fieldset-wide']
        );

        $wysiwygConfig = $this->_wysiwygConfig->getConfig();
        $html = $htmlFieldset->addField(
            'html',
            'editor',
            [
                'name' => 'html',
                'label' => __('Content'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'config' => $wysiwygConfig
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        // define field dependencies
        // $this->setChild(
        //     'form_after',
        //     $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
        //         ->addFieldMap($mode->getHtmlId(), $mode->getName())
        //         ->addFieldMap($title->getHtmlId(), $title->getName())
        //         ->addFieldMap($image->getHtmlId(), $image->getName())
        //         ->addFieldMap($width->getHtmlId(), $width->getName())
        //         ->addFieldMap($height->getHtmlId(), $height->getName())
        //         ->addFieldMap($resize->getHtmlId(), $resize->getName())
        //         ->addFieldMap($retina->getHtmlId(), $retina->getName())
        //         ->addFieldMap($background->getHtmlId(), $background->getName())
        //         ->addFieldMap($html->getHtmlId(), $html->getName())
        //         ->addFieldDependence($title->getName(), $mode->getName(), 'image')
        //         ->addFieldDependence($image->getName(), $mode->getName(), 'image')
        //         ->addFieldDependence($width->getName(), $mode->getName(), 'image')
        //         ->addFieldDependence($height->getName(), $mode->getName(), 'image')
        //         ->addFieldDependence($resize->getName(), $mode->getName(), 'image')
        //         ->addFieldDependence($retina->getName(), $mode->getName(), 'image')
        //         ->addFieldDependence($background->getName(), $mode->getName(), 'image')
        //         ->addFieldDependence($html->getName(), $mode->getName(), 'html')
        // );

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Content');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Content');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
