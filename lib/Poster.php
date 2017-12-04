<?php
/**
 * Description of Poster
 *
 * @author gregzorb
 */
require_once('lib/Curl.php');
require_once('lib/Config.php');

class Poster
{
    private $transport,
        $configs;
    public $transactionStatuses = array(
        1 => "открыт",
        2 => "закрыт",
        3 => "удален",
    );

    const STATUS_POSTER_OPENED  = 1;
    const STATUS_POSTER_CLOSE   = 2;
    const STATUS_POSTER_DELETED = 3;
    const STATUS_WAITING = 101;
    const STATUS_DONE    = 102;

    public function __construct()
    {
        date_default_timezone_set('UTC');
        $this->transport = new Curl();
        $configClass     = new Config();
        $this->configs   = $configClass->configs;

        //write from db
        $crud    = new Crud();
        $configs = $crud->getSettings();
        if (!empty($configs)) {
            foreach ($configs as $config) {
                $this->configs[$config['alias']] = $config['value'];
            }
        }
    }

    public function getLastTransactions()
    {
        $yesterday = date("Ymd", time() - 60 * 60 * 24);

        $params = array(
            'include_products' => false,
            'dateFrom' => $yesterday,
            'token' => $this->configs['token'],
        );

        $url = $this->configs['url'].'dash.getTransactions?'.http_build_query($params);

        $transactions = $this->transport->sendRequest($url);
        if (empty($transactions['response'])) {
            return array();
        }

        $transactions = $this->mapTransactions($transactions);

        $result = array();
        if (isset($transactions[self::STATUS_DONE])) {
            $sorted = $this->sortTransactions($transactions[self::STATUS_DONE]);
            $result = array_merge($result, $sorted);
        }

        if (isset($transactions[self::STATUS_WAITING])) {
            $sorted = $this->sortTransactions($transactions[self::STATUS_WAITING]);
            $result = array_merge($result, $sorted);
        }

        $result = array_slice($result, 0, 8);


        return $result;
    }

    protected function mapTransactions($transactions)
    {
        $resultByStatus = array();
        foreach ($transactions['response'] as $t) {
            $status = $t['status'];

            //filter by deleted
            if ($status == self::STATUS_POSTER_DELETED) continue;

            $status = self::STATUS_WAITING;
            if (strpos($t['transaction_comment'], $this->configs['doneComment'])
                > -1) {
                $status = self::STATUS_DONE;
            }

            $row = array(
                'id' => $t['transaction_id'],
                'status' => $status,
                'origin_status' => $t['status'],
                'last_date' => date("Y-m-d H:i:s"),
            );

            if ($t['date_close'] > 0) {
                $row['last_date'] = date("Y-m-d H:i:s", (int)round($t['date_close'] / 1000) );
            } elseif ($t['date_start'] > 0) {
                $row['last_date'] = date("Y-m-d H:i:s", (int)round($t['date_start'] / 1000) );
            }

            $resultByStatus[$status][] = $row;
        }

        if (empty($resultByStatus)) {
            return array();
        }
        return $resultByStatus;
    }

    protected function sortTransactions($result)
    {
        function cmp($a, $b) {
            if (strtotime($a['last_date']) < strtotime($b['last_date'])) {
                return 1;
            }
        }

        usort($result, "cmp" );
        return $result;
    }
}