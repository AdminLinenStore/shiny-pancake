<?php
namespace Swissup\Reviewreminder\Observer;

class SendReviewreminders
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
     * @param \Swissup\Reviewreminder\Helper\Config $configHelper
     * @param \Swissup\Reviewreminder\Helper\Helper $helper
     */
    public function __construct(
        \Swissup\Reviewreminder\Helper\Config $configHelper,
        \Swissup\Reviewreminder\Helper\Helper $helper
    ) {
        $this->configHelper = $configHelper;
        $this->helper = $helper;
    }
    /**
     * Check order status and save it to review reminder table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute()
    {
        if ($this->configHelper->isEnabled()) {
            $this->helper->sendReminders(null);
        }
    }
}
