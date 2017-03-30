<?php
namespace Swissup\Askit\Controller\Question;

use Swissup\Askit\Api\Data\ItemInterface;
use Magento\Store\Model\ScopeInterface;

class Save extends \Magento\Framework\App\Action\Action
{
    const ASKIT_DEFAULT_QUESTION_STATUS = 'askit/general/defaultQuestionStatus';
    const ASKIT_ALLOWED_GUEST_QUESTION  = 'askit/general/allowedGuestQuestion';

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    protected function _redirectReferer()
    {
        $this->_redirect($this->_redirect->getRedirectUrl());
    }

    protected function _getConfig($key)
    {
        return $this->_scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Post user question
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();

        if (!$post) {
            $this->_redirectReferer();
            return;
        }

        $isLoggedIn = $this->_customerSession->isLoggedIn();
        $customer = $this->_customerSession->getCustomer();
        $isAllowedGuestQuestion = $this->_getConfig(self::ASKIT_ALLOWED_GUEST_QUESTION);

        if (!$isLoggedIn && !$isAllowedGuestQuestion) {
            $this->messageManager->addError(__('Your must login'));
            $this->_redirectReferer();
            return;
        }

        try {

            $error = false;

            if (!\Zend_Validate::is(trim($post['customer_name']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($post['text']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                $error = true;
            }

            if ($error) {
                throw new \Exception();
            }
            $post['customer_id'] = $isLoggedIn ? $customer->getId() : null;

            $post['store_id'] = $this->_storeManager->getStore()->getId();

            $post['status'] = $this->_getConfig(self::ASKIT_DEFAULT_QUESTION_STATUS);

            $model = $this->_objectManager->create('Swissup\Askit\Model\Item');

            $model->setData($post);

            $model->save();

            $this->_eventManager->dispatch(
                'askit_item_after_save',
                ['item' => $model, 'request' => $this->getRequest()]
            );

            $this->messageManager->addSuccess(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->_redirectReferer();
            return;
        } catch (\Exception $e) {
            // $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
            $this->_redirectReferer();
            return;
        }
    }
}
