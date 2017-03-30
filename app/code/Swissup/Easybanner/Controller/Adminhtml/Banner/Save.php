<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
    /**
     * upload model
     *
     * @var \Swissup\Easybanner\Model\Data\Upload
     */
    protected $uploadModel;
    /**
     * image model
     *
     * @var \Swissup\Easybanner\Model\Data\Image
     */
    protected $imageModel;


    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context,
        \Swissup\Easybanner\Model\Data\Image $imageModel,
        \Swissup\Easybanner\Model\Data\Upload $uploadModel)
    {
        $this->uploadModel = $uploadModel;
        $this->imageModel = $imageModel;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_Easybanner::banner_save');
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
            $bannerModel = $this->_objectManager->create('Swissup\Easybanner\Model\Banner');

            $id = $this->getRequest()->getParam('banner_id');
            if ($id) {
                $bannerModel->load($id);
            }

            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);

            $bannerModel->loadPost($data);

            $imageName = $this->uploadModel->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $data);
            $bannerModel->setImage($imageName);
            try {
                // Save Banner
                $bannerModel->save();
                $this->messageManager->addSuccess(__('Banner has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['banner_id' => $bannerModel->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the banner.'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
