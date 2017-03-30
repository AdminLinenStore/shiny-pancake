<?php
namespace Swissup\Askit\Ui\DataProvider\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
// use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Swissup\Askit\Model\ResourceModel\Question\Grid\CollectionFactory;
// use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Swissup\Askit\Model\ResourceModel\Question\Grid\Collection;
use Magento\Review\Model\Review;

/**
 * Class QuestionDataProvider
 *
 * @method Collection getCollection
 */
class QuestionDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
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

        $this->getCollection()
            ->addQuestionFilter(0)
        ;

        // $customerId = $this->request->getParam('current_customer_id', false);
        // if ($customerId) {
        //     $this->getCollection()->addCustomerFilter($customerId);
        // }

        $productId = $this->request->getParam('current_product_id', false);
        if ($productId) {
            $this->getCollection()->addProductFilter($productId);
        }

        $categoryId = $this->request->getParam('current_category_id', false);

        if ($categoryId) {
            $this->getCollection()->addCategoryFilter($categoryId);
        }

        $pageId = $this->request->getParam('current_page_id', false);
        if ($pageId) {
            $this->getCollection()->addPageFilter($pageId);
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
}
