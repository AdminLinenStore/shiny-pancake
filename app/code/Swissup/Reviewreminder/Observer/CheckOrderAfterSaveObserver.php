<?php
namespace Swissup\Reviewreminder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Captcha\Observer\CaptchaStringResolver;

class CheckOrderAfterSaveObserver implements ObserverInterface
{
    /**
     * @var \Swissup\Reviewreminder\Helper\Config
     */
    protected $configHelper;
    /**
     * @var \Swissup\Reviewreminder\Helper\Helper
     */
    protected $helper;
    /**
     * @var \Swissup\Reviewreminder\Model\EntityFactory
     */
    protected $reminderFactory;
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;
    /**
     * @param \Swissup\Reviewreminder\Helper\Config $configHelper
     * @param \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory
     * @param \Swissup\Reviewreminder\Helper\Helper $helper
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        \Swissup\Reviewreminder\Helper\Config $configHelper,
        \Swissup\Reviewreminder\Model\EntityFactory $reminderFactory,
        \Swissup\Reviewreminder\Helper\Helper $helper,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        $this->configHelper = $configHelper;
        $this->reminderFactory = $reminderFactory;
        $this->helper = $helper;
        $this->mathRandom = $mathRandom;
    }
    /**
     * Check order status and save it to review reminder table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->configHelper->isEnabled()) {
            $order = $observer->getOrder();
            if ($this->configHelper->allowSpecificStatuses()) {
                $orderStatus = $order->getStatus();
                $allowedStatuses = $this->configHelper->specificOrderStatuses();
                if (in_array($orderStatus, $allowedStatuses)) {
                    $this->saveOrder($order);
                }
            } else {
                $this->saveOrder($order);
            }
        }
    }
    /**
     * Check order id and save it to review reminder table
     */
    protected function saveOrder($order)
    {
        $model = $this->reminderFactory->create();
        $collection = $model->getCollection()
            ->addFieldTofilter('order_id', $order->getId());
        if ($collection->getSize() == 0) {
            $model->setOrderId($order->getId());
            $model->setCustomerEmail($order->getCustomerEmail());
            $model->setStatus($this->configHelper->getDefaultStatus());
            $model->setOrderDate($this->helper->getOrderDate($order, $this->configHelper));
            $model->setHash($this->mathRandom->getRandomString(16));
            $model->save();
        }
    }
}
