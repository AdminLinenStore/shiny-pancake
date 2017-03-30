<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Placeholder;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_Easybanner::placeholder_save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Swissup\ProLabels\Model\Label $model */
            $placeholderModel = $this->_objectManager->create('Swissup\Easybanner\Model\Placeholder');

            $id = $this->getRequest()->getParam('placeholder_id');
            if ($id) {
                $placeholderModel->load($id);
            }
            $placeholderModel->addData($data);
            try {
                // Save Banner
                $placeholderModel->save();
                $this->messageManager->addSuccess(__('Placeholder has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['placeholder_id' => $placeholderModel->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the placeholder.'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
