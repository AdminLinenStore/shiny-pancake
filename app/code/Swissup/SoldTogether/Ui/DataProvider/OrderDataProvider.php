<?php
namespace Swissup\SoldTogether\Ui\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Swissup\SoldTogether\Model\ResourceModel\Order\Grid\CollectionFactory;
use Swissup\SoldTogether\Model\ResourceModel\Order\Grid\Collection;

class OrderDataProvider extends AbstractDataProvider
{

    protected $collectionFactory;

    protected $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $productId = $this->request->getParam('current_product_id', false);
        if ($productId) {
            $this->getCollection()->addProductFilter($productId);
        }

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $arrItems['items'][] = $item->toArray([]);
        }

        return $arrItems;
    }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function addFilter(\Magento\Framework\Api\Filter $filter)
    // {
    //     $field = $filter->getField();

    //     if (in_array($field, ['review_id', 'created_at', 'status_id'])) {
    //         $filter->setField('rt.' . $field);
    //     }

    //     if (in_array($field, ['title', 'nickname', 'detail'])) {
    //         $filter->setField('rdt.' . $field);
    //     }

    //     if ($field === 'review_created_at') {
    //         $filter->setField('rt.created_at');
    //     }

    //     parent::addFilter($filter);
    // }
}
