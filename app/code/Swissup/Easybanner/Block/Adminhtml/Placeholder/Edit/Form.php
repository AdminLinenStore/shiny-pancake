<?php
namespace Swissup\Easybanner\Block\Adminhtml\Placeholder\Edit;

/**
 * Adminhtml placeholder edit form main tab
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('placeholder_form');
        $this->setTitle(__('Placeholder Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Swissup\Testimonial\Model\Data $model */
        $model = $this->_coreRegistry->registry('easybanner_placeholder');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Swissup_Easybanner::placeholder_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('placeholder_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Placeholder')]);

        if ($model->getPlaceholderId()) {
            $fieldset->addField('placeholder_id', 'hidden', ['name' => 'placeholder_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'limit',
            'text',
            [
                'name' => 'limit',
                'label' => __('Banners limit per rotate'),
                'title' => __('Banners limit per rotate'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $field = $fieldset->addField(
            'sort_mode',
            'select',
            [
                'name' => 'sort_mode',
                'label' => __('Sort mode'),
                'title' => __('Sort mode'),
                'required' => true,
                'options'  => [
                    'sort_order' => __('By Banners Sort Order'),
                    'random' => __('Random')
                ],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')],
                'disabled' => $isElementDisabled
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
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