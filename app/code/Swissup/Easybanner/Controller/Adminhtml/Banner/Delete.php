<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

class Delete extends \Magento\Backend\App\Action
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_Easybanner::banner_delete');
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
        $id = $this->getRequest()->getParam('banner_id');
        if ($id) {
            try {
                $bannerModel = $this->_objectManager->create('Swissup\Easybanner\Model\Banner');
                $bannerModel->load($id);
                $bannerModel->delete();
                $this->messageManager->addSuccess(__('Banner was deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['banner_id' => $id]);
            }
        }
        $this->messageManager->addError(__('Can\'t find a banner to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
