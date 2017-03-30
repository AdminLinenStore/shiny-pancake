<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Placeholder;

class Enable extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_Easybanner::placeholder_save');
    }
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('placeholder_id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Swissup\Easybanner\Model\Placeholder');
                $model->load($id);
                $model->setStatus(1);
                $model->save();
                $this->messageManager->addSuccess(__('Placeholder was enabled.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['placeholder_id' => $id]);
            }
        }
        $this->messageManager->addError(__('Can\'t find a placeholder to enable.'));
        return $resultRedirect->setPath('*/*/');
    }
}
