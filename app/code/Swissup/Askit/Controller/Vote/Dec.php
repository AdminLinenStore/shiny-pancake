<?php
namespace Swissup\Askit\Controller\Vote;

use Swissup\Askit\Api\Data\ItemInterface;

class Dec extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
    }

    protected function _redirectReferer()
    {
        $this->_redirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Post user question
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        if (!$id || !$this->_customerSession->isLoggedIn()) {
            $this->messageManager->addError(
                __('Sorry, only logged in customer can hint.')
            );
            $this->_redirectReferer();
            return;
        }

        try {

            $customerId = $this->_customerSession->getId();

            $modelVote = $this->_objectManager->create('Swissup\Askit\Model\Vote');
            if ($modelVote->isVoted($id, $customerId)) {
                $this->messageManager->addError(
                    __('Sorry, already voted')
                );
                $this->_redirectReferer();
                return;
            }

            $modelItem = $this->_objectManager->create('Swissup\Askit\Model\Item');
            $modelItem->load($id);

            $modelItem->setHint($modelItem->getHint() - 1);
            $modelItem->save();

            $modelVote->setData([
                'item_id' => $id,
                'customer_id' => $customerId
            ])->save();

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
