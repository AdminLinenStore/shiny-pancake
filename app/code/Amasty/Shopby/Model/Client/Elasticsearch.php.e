<?php
namespace Amasty\Shopby\Model\Client;

use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;

class Elasticsearch extends \Magento\Elasticsearch\Model\Client\Elasticsearch implements ClientInterface
{
    /** @var  ResourceConnection */
    protected $resourceConnection;

    function __construct(array $options = [], ResourceConnection $resourceConnection)
    {
        parent::__construct($options, null);
        $this->resourceConnection = $resourceConnection;
    }

    public function query($query)
    {
        array_walk_recursive($query, function (&$item, $key) {
            if ($key == '_id' && $item instanceof Table) {
                $item = $this->extractIdsFromTable($item);
            }
        });

        return parent::query($query);
    }

    protected function extractIdsFromTable(Table $table)
    {
        $connection = $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $tableName = $connection->getTableName($table->getName());
        $ids = $connection->fetchCol('SELECT `entity_id` FROM ' . $tableName);
        return $ids;
    }
}
