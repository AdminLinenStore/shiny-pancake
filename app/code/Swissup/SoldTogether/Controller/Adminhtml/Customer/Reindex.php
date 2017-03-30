<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;

class Reindex extends \Magento\Backend\App\Action
{
    const CUSTOMER_SIZE = 1;
    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->_scopeConfig   = $scopeConfig;
        parent::__construct($context);
    }
    /**
     * Index orders action
     *
     */
    public function execute()
    {
        $customerModel = $this->_objectManager->create('Swissup\SoldTogether\Model\Customer');
        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
        if (!$session->hasData("swissup_soldtogether_customer_init")) {
            $customerModel->deleteAllCustomerRelations();
            $customerCollection = $this->_objectManager->get('Magento\Customer\Model\ResourceModel\Customer\Collection');
            $customerCount = count($customerCollection->getAllIds());
            if ($customerCount == 0) {
                $this->messageManager->addNotice(__('We couldn\'t find any customers'));
                return $this->getResponse()->setBody(
                    $this->jsonEncoder->encode(array(
                        'finished'  => true
                    ))
                );
            }
            $session->setData("swissup_soldtogether_customer_init", 1);
            $session->setData("swissup_soldtogether_customer_count", $customerCount);
            $session->setData("swissup_soldtogether_customer_step", 0);
            $session->setData("swissup_soldtogether_customer_success", 0);

            $percent = 100 * (int)$session->getData("swissup_soldtogether_customer_success") / (int)$session->getData("swissup_soldtogether_customer_count");
            $responseLoaderText = $session->getData("swissup_soldtogether_customer_success")
                . ' of ' . $session->getData("swissup_soldtogether_customer_count") . ' - ' . (int)$percent . '%';
            $this->getResponse()->setBody(
                $this->jsonEncoder->encode(array(
                    'finished'  => false,
                    'loaderText' => $responseLoaderText
                ))
            );
        }
        if ($session->getData("swissup_soldtogether_customer_count") == $session->getData("swissup_soldtogether_customer_success")) {
            $session->unsetData("swissup_soldtogether_customer_init");
            $session->unsetData("swissup_soldtogether_customer_count");
            $session->unsetData("swissup_soldtogether_customer_step");
            $session->unsetData("swissup_soldtogether_customer_success");

            $this->messageManager->addSuccess(__('All Customer Orders have been indexed.'));
            return $this->getResponse()->setBody(
                $this->jsonEncoder->encode(array(
                    'finished'  => true
                ))
            );
        }
        /*
        ** Index Orders
         */
        $step = $session->getData("swissup_soldtogether_customer_step");
        $productIds = $customerModel->getCustomerOrderIds(self::CUSTOMER_SIZE, $step);
        $productCount = count($productIds);
        if ($productCount > 0) {
            $result = [];
            foreach ($productIds as $productId => $orderData) {
                foreach ($productIds as $relatedId => $relatedData) {
                    if ($productId == $relatedId) { continue; }
                    if ($orderData['store'] != $relatedData['store']) { continue; }
                    $result[] = [
                        'product_id'   => $productId,
                        'related_id'   => $relatedId,
                        'product_name' => $orderData['name'],
                        'related_name' => $relatedData['name'],
                        'store_id'     => 0,
                        'weight'       => 1,
                        'is_admin'     => 0
                    ];
                }
            }
            if (count($result) > 0) {
                // add data to db
                $dbData = [];
                foreach ($result as $item) {
                    $itemExist = $customerModel->relationExist($item['product_id'], $item['related_id'], 0);
                    if ($itemExist) {
                        try {
                            $customerModel->load($itemExist);
                            if (!(int)$customerModel->getIsAdmin()) {
                                $customerModel->setWeight($customerModel->getWeight() + 1);
                                $customerModel->save();
                            }
                        } catch (\Exception $e) {
                            continue;
                        }
                    } else {
                        $dbData[] = $item;
                    }
                }
                if (count($dbData) > 0) {
                    try {
                        $this->connection->insertMultiple(
                            $this->resource->getTableName('swissup_soldtogether_customer'), $dbData);
                    } catch (\Exception $e) {

                    }
                }
            }

            $session->setData("swissup_soldtogether_customer_step", $session->getData("swissup_soldtogether_customer_step") + 1);
            $session->setData("swissup_soldtogether_customer_success", $session->getData("swissup_soldtogether_customer_success") + 1);

            $percent = 100 * (int)$session->getData("swissup_soldtogether_customer_success") / (int)$session->getData("swissup_soldtogether_customer_count");
            $responseLoaderText = $session->getData("swissup_soldtogether_customer_success")
                . ' of ' . $session->getData("swissup_soldtogether_customer_count") . ' - ' . (int)$percent . '%';
            $this->getResponse()->setBody(
                $this->jsonEncoder->encode(array(
                    'finished'  => false,
                    'loaderText' => $responseLoaderText
                ))
            );
        }
    }
}
