<?php
namespace Swissup\Askit\Ui\DataProvider\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Swissup\Askit\Model\ResourceModel\Answer\Grid\CollectionFactory;
use Swissup\Askit\Model\ResourceModel\Answer\Grid\Collection;
use Magento\Review\Model\Review;

/**
 * Class QuestionDataProvider
 *
 * @method Collection getCollection
 */
class AnswerDataProvider extends AbstractDataProvider
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
            ->addAnswerFilter()
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
        // $c = $this->getCollection();
        // \Zend_Debug::dump((string) $c->getSelect(), __METHOD__);
        // die;

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
