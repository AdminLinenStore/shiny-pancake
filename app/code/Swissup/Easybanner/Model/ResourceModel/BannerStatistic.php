<?php
namespace Swissup\Easybanner\Model\ResourceModel;

/**
 * BannerStatistic mysql resource
 */
class BannerStatistic extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_easybanner_banner_statistic', 'id');
    }

    public function incrementDisplayCount($bannerId)
    {
        $todayDate = $this->_date->gmtDate('Y-m-d');
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('banner_id = ?', $bannerId)
            ->where('date = ?', $todayDate);

        $row = $connection->fetchRow($select);

        if ($row) {
            $connection->update($this->getMainTable(), array(
                'display_count' => ++$row['display_count']
                ), "banner_id = {$bannerId} AND date = '{$todayDate}'"
            );
        } else {
            $connection->insert($this->getMainTable(), array(
                'banner_id' => $bannerId,
                'date' => $todayDate,
                'display_count' => 1,
                'clicks_count' => 0
            ));
        }
    }

    public function incrementClicksCount($bannerId)
    {
        $todayDate = $this->_date->gmtDate('Y-m-d');
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('banner_id = ?', $bannerId)
            ->where('date = ?', $todayDate);

        $row = $connection->fetchRow($select);
        if ($row) {
            $connection->update($this->getMainTable(), array(
                'clicks_count' => ++$row['clicks_count']
                ), "banner_id = {$bannerId} AND date = '{$todayDate}'"
            );
        } else {
            $connection->insert($this->getMainTable(), array(
                'banner_id' => $bannerId,
                'date' => $todayDate,
                'display_count' => 1,
                'clicks_count' => 1
            ));
        }
    }

    public function getChartStatisticData($bannerId, $type)
    {
        $statistics = [];
        $charts = [];
        $charts[] = ['Date', 'Display', 'Clicks'];
        $date = date('Y-m-d', strtotime('-7 days'));
        $connection = $this->getConnection();
        switch ($type) {
            case '1':
                $select = $connection->select()
                    ->from($this->getMainTable())
                    ->where('banner_id = ?', $bannerId)
                    ->where('date >= ?', $date);

                $result = $connection->fetchAll($select);
                foreach ($result as $item) {
                    $statistics[$item['date']] = [
                        'display_count' => (int)$item['display_count'],
                        'clicks_count' => (int)$item['clicks_count']
                    ];
                }
                for ($i=7; $i >=1 ; $i--) {
                    $date = date('Y-m-d', strtotime(-$i . ' days'));
                    if (array_key_exists($date, $statistics)) {
                        $charts[] = [
                            $date,
                            $statistics[$date]['display_count'],
                            $statistics[$date]['clicks_count']
                        ];
                    } else {
                        $charts[] = [$date, 0, 0];
                    }
                }
                return $charts;
                break;
            case '2':
                $todayDate = $this->_date->gmtDate('Y-m-d');
                $dateArray = explode('-', $todayDate);
                $monthStart = $dateArray[0] . '-' . $dateArray[1] . '-01';
                $select = $connection->select()
                    ->from($this->getMainTable())
                    ->where('banner_id = ?', $bannerId)
                    ->where('date >= ?', $monthStart);

                $result = $connection->fetchAll($select);
                foreach ($result as $item) {
                    $statistics[$item['date']] = [
                        'display_count' => (int)$item['display_count'],
                        'clicks_count' => (int)$item['clicks_count']
                    ];
                }

                for ($i=$dateArray[2]; $i >=1 ; $i--) {
                    $date = date('Y-m-d', strtotime(-$i . ' days'));
                    if (array_key_exists($date, $statistics)) {
                        $charts[] = [
                            $date,
                            $statistics[$date]['display_count'],
                            $statistics[$date]['clicks_count']
                        ];
                    } else {
                        $charts[] = [$date, 0, 0];
                    }
                }
                return $charts;
                break;
            case '3':
                $monthStart = date('Y-m-d', strtotime('-6 month'));
                $select = $connection->select()
                    ->from($this->getMainTable())
                    ->where('banner_id = ?', $bannerId)
                    ->where('date >= ?', $monthStart);

                $result = $connection->fetchAll($select);
                foreach ($result as $item) {
                    $charts[] = [
                        $item['date'],
                        (int)$item['display_count'],
                        (int)$item['clicks_count']
                    ];
                }
                return $charts;
                break;
            case '4':
                $monthStart = date('Y-m-d', strtotime('-12 month'));
                $select = $connection->select()
                    ->from($this->getMainTable())
                    ->where('banner_id = ?', $bannerId)
                    ->where('date >= ?', $monthStart);

                $result = $connection->fetchAll($select);
                foreach ($result as $item) {
                    $charts[] = [
                        $item['date'],
                        (int)$item['display_count'],
                        (int)$item['clicks_count']
                    ];
                }
                return $charts;
                break;
            case '5':
                $select = $connection->select()
                    ->from($this->getMainTable())
                    ->where('banner_id = ?', $bannerId);

                $result = $connection->fetchAll($select);
                foreach ($result as $item) {
                    $charts[] = [
                        $item['date'],
                        (int)$item['display_count'],
                        (int)$item['clicks_count']
                    ];
                }
                return $charts;
                break;
        }
        return [];
    }
}
