<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;

class Reindex extends \Magento\Backend\App\Action
{
    const ORDER_SIZE = 10;
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
        $orderModel = $this->_objectManager->create('Swissup\SoldTogether\Model\Order');
        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
        if (!$session->hasData("swissup_soldtogether_order_init")) {
            $orderModel->deleteAllOrderRelations();
            $orderCollection = $this->_objectManager->get('Magento\Sales\Model\ResourceModel\Order\Collection');
            $orderCount = count($orderCollection->getAllIds());
            if ($orderCount == 0) {
                $this->messageManager->addNotice(__('We couldn\'t find any orders'));
                return $this->getResponse()->setBody(
                    $this->jsonEncoder->encode(array(
                        'finished'  => true
                    ))
                );
            }
            $session->setData("swissup_soldtogether_order_init", 1);
            $session->setData("swissup_soldtogether_order_count", $orderCount);
            $session->setData("swissup_soldtogether_order_step", 0);
            $session->setData("swissup_soldtogether_order_success", 0);

            $percent = 100 * (int)$session->getData("swissup_soldtogether_order_success") / (int)$session->getData("swissup_soldtogether_order_count");
            $responseLoaderText = $session->getData("swissup_soldtogether_order_success")
                . ' of ' . $session->getData("swissup_soldtogether_order_count") . ' - ' . (int)$percent . '%';
            $this->getResponse()->setBody(
                $this->jsonEncoder->encode(array(
                    'finished'  => false,
                    'loaderText' => $responseLoaderText
                ))
            );
        }

        /*
        ** Index Orders
         */
        $step = $session->getData("swissup_soldtogether_order_step");
        $orderIds = $orderModel->getOrderIdsToReindex(self::ORDER_SIZE, $step);
        $orderCount = count($orderIds);
        if ($orderCount > 0) {
            $orderCollection = $this->_objectManager->get('Magento\Sales\Model\ResourceModel\Order\Collection');
            $orderCollection->addAttributeToFilter('entity_id', array('in' => $orderIds));
            $result = [];
            foreach ($orderCollection as $order) {
                $storeId = $order->getStoreId();
                $visibleItems = $order->getAllVisibleItems();
                $orderProducts = [];
                if (count($visibleItems) > 1) {
                    foreach ($visibleItems as $product) {
                        $orderProducts[$product->getProductId()] = $product->getName();
                    }
                    foreach ($orderProducts as $productId => $productName) {
                        foreach ($orderProducts as $relatedId => $relatedName) {
                            if ($productId == $relatedId) { continue; }
                            $result[] = [
                                'product_id'   => $productId,
                                'related_id'   => $relatedId,
                                'product_name' => $productName,
                                'related_name' => $relatedName,
                                'store_id'     => 0,
                                'weight'       => 1,
                                'is_admin'     => 0
                            ];
                        }
                    }
                }
            }
            if (count($result) > 0) {
                // add data to db
                $dbData = [];
                foreach ($result as $item) {
                    $itemExist = $orderModel->relationExist($item['product_id'], $item['related_id'], 0);
                    if ($itemExist) {
                        try {
                            $orderModel->load($itemExist);
                            if (!(int)$orderModel->getIsAdmin()) {
                                $orderModel->setWeight($orderModel->getWeight() + 1);
                                $orderModel->save();
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
                            $this->resource->getTableName('swissup_soldtogether_order'), $dbData);
                    } catch (\Exception $e) {

                    }
                }
            }

            $session->setData("swissup_soldtogether_order_step", $session->getData("swissup_soldtogether_order_step") + 1);
            $session->setData("swissup_soldtogether_order_success", $session->getData("swissup_soldtogether_order_success") + $orderCount);

            $percent = 100 * (int)$session->getData("swissup_soldtogether_order_success") / (int)$session->getData("swissup_soldtogether_order_count");
            $responseLoaderText = $session->getData("swissup_soldtogether_order_success")
                . ' of ' . $session->getData("swissup_soldtogether_order_count") . ' - ' . (int)$percent . '%';
            $this->getResponse()->setBody(
                $this->jsonEncoder->encode(array(
                    'finished'  => false,
                    'loaderText' => $responseLoaderText
                ))
            );
        } else {
            $session->unsetData("swissup_soldtogether_order_init");
            $session->unsetData("swissup_soldtogether_order_count");
            $session->unsetData("swissup_soldtogether_order_step");
            $session->unsetData("swissup_soldtogether_order_success");
            $this->messageManager->addSuccess(__('All Orders have been indexed.'));

            return $this->getResponse()->setBody(
                $this->jsonEncoder->encode(array(
                    'finished'  => true
                ))
            );
        }
    }
}
