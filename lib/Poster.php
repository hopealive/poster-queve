<?php
/**
 * Description of Poster
 *
 * @author gregzorb
 */

class Poster
{
    private $transport,
        $configs;
    
    public $transactionStatuses = array(
        1 => "відкритий",
        2 => "закрий",
        3 => "видалений",
    );

    const STATUS_POSTER_OPENED  = 1;
    const STATUS_POSTER_CLOSE   = 2;
    const STATUS_POSTER_DELETED = 3;
    const STATUS_POSTER_FISCAL = 4;
    const STATUS_WAITING        = 'waiting';
    const STATUS_DONE           = 'done';
    const STATUS_CLOSE = 'closed';
    const STATUS_DELETED = 'deleted';
    const STATUS_FISCAL = 'fiscal';


    public function __construct()
    {
        date_default_timezone_set('UTC');
        $this->transport = new Curl();

        $settings = parse_ini_file(ROOT.DS."db/settings.ini.php");
        $this->configs   = array(
            'url' => $settings['demo_url'],
            'token' => $settings['demo_token'],
            'doneComment' => $settings['demo_doneComment'],
        );

        //write from db
        $configs = (new Settings)->getSettings();
        if (!empty($configs)) {
            foreach ($configs as $config) {
                $this->configs[$config['alias']] = $config['value'];
            }
        }
    }

    public function getLastTransactions()
    {
        $dateFrom = date("Ymd", time() - 60 * 60 * 24);
        $params = array('dateFrom' => $dateFrom);
        $response = $this->getTransactions($params);
        $transactions = $this->processTransactions($response);
        if ($transactions['status'] == 'error' || empty($transactions['transactions'])  ) return $transactions;

        $converted = $this->convertedByStatus($transactions['transactions']);
        return [
            'status' => $transactions['status'],
            'message' => $transactions['message'],
            'transactions' => array_slice($converted, 0, 8),
            'changedToComplete' => $transactions['changedToComplete'],
        ];
    }

    public function getPaginateTransactions($offset, $length)
    {
        $params = array();
        if ( $length > 0 ) $params['limit'] = $length;
        if ( $offset > 0 ) $params['offset'] = $length*$offset;


        $orders = (new Orders())->getAll($params);
        if (empty($orders)){
            return [
                'status' => "error",
                'message' => 'Немає замовлень',
                'transactions' => array(),
            ];
        }

        foreach ( $orders as $order ){
            $resultByStatus[$order['status']][] = $order;
        }
        $result = $this->convertedByStatus($resultByStatus);

        return [
            'status' => 'success',
            'message' => 'Success',
            'transactions' => $result,
        ];
    }

    public function getTransactionTotal()
    {
        return (new Orders())->countAll();
    }

    protected function getTransactions($params = array() )
    {
        if ( isset($params['dateFrom'])){
            $dateFrom = $params['dateFrom'];
        }

        $params = array(
            'include_products' => false,
            'dateFrom' => $dateFrom,
            'token' => $this->configs['token'],
        );

        $url = $this->configs['url'].'dash.getTransactions?'.http_build_query($params);
        return $this->transport->sendRequest($url);
    }

    protected function processTransactions($response)
    {
        if (empty($response)){
            return [
                'status' => 'error',
                'message' => 'Немає відповіді від серверу',
                'transactions' => array(),
                'changedToComplete' => $changedToComplete,
            ];
        }

        if (isset($response['error'])){
            return [
                'status' => 'error',
                'message' => $response['error']['message'],
                'transactions' => array(),
                'changedToComplete' => $changedToComplete,
            ];
        }

        $transactions = $response['response'];
        if (empty($transactions)){
            return [
                'status' => 'success',
                'message' => 'Немає замовлень',
                'transactions' => array(),
                'changedToComplete' => $changedToComplete,
            ];
        }

        (new OrderHistory())->moveFromOrders();
        $changedToComplete = false;

        $nOrders = array();
        foreach ($transactions as $k => $t) {
//todo: remove, for test
// if( !($t['transaction_id']  % 5)) $t['status'] = self::STATUS_POSTER_OPENED;//every 5th - opened
// if( !($t['transaction_id']  % 10)) $t['transaction_comment'] = "+";//every 10th - complete

            $order = array(
                'origin_id' => $t['transaction_id'],
                'view_id' => null,
                'status' => $this->getInnerStatus($t),
                'origin_status' => $t['status'],
                'last_date' => $this->getLastDate($t),
            );

            if ( empty($order['last_date']) OR $order['last_date'] < date("Y-m-d 00:00:00") ) continue;

            $nOrders[$t['transaction_id']] = $order;
        }

        $exists = array();
        $existOrders = array();
        if (!empty($nOrders)){
            $exists = (new Orders())->getListByOriginIds(array_column($nOrders, 'origin_id'));
            if (!empty($exists)){
                foreach ($exists as $existId) {
                    //for check to update
                    $existOrders[$existId] = $nOrders[$existId];

                    //remove from working in future
                    unset($nOrders[$existId]);
                }
            }
        }

        //sort
        if (!empty($nOrders)){
            uksort($nOrders, function($a, $b) {
                return ($a['last_date'] < $b['last_date']) ? -1 : 1;
            });
        }

        $i = $maxId = (new Orders())->getMaxId();
        foreach ( $nOrders as $originId => $order ){
            ++$i;
            $nOrders[$originId]['view_id'] = $i;
        }

        //create new orders
        (new Orders())->createList($nOrders);

        $viewIds = (new Orders())->getViewIdList();

        if (!empty($existOrders)){
            $params['filters'] = ['origin_id' => 'IN ( '.implode(",", array_keys($existOrders) ).' )'];
            $existInDbOrders = (new Orders())->getAll($params);
            foreach ( $existInDbOrders as $eOrder ){
                $needUpdate = false;
                $originId = $eOrder['origin_id'] ;
                if ( $eOrder['origin_status'] != $existOrders[$originId]['origin_status'] ){
                    $needUpdate = true;
                }

                if ( $eOrder['status'] != $existOrders[$originId]['status']  ){
                    $needUpdate = true;
                    if ( $existOrders[$originId]['status'] == self::STATUS_DONE){
                        $changedToComplete = true;
                    }
                }

                //update
                if ( $needUpdate ){
                    (new Orders())->updateStatus($existOrders[$originId]);
                }
            }
        }


        $resultByStatus = array();
        foreach ($transactions as $t) {
            $status = $this->getInnerStatus($t);
            $resultByStatus[$status][] = array(
                'id' => $t['transaction_id'],
                'view_id' => (isset($viewIds[$t['transaction_id']]) ? $viewIds[$t['transaction_id']] : "-"),
                'status' => $status,
                'origin_status' => $t['status'],
                'last_date' => $this->getLastDate($t),
            );
        }

        if (empty($resultByStatus)) {
            return [
                'status' => 'error',
                'message' => 'Error while grouping transactions',
                'transactions' => array(),
                'changedToComplete' => $changedToComplete,
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Success',
            'transactions' => $resultByStatus,
            'changedToComplete' => $changedToComplete,
        ];
    }

    protected function getInnerStatus($t)
    {
        if ($t['status'] == self::STATUS_POSTER_DELETED) return self::STATUS_DELETED;
        if ($t['status'] == self::STATUS_POSTER_CLOSE) return self::STATUS_CLOSE;
        if ($t['status'] == self::STATUS_POSTER_FISCAL) return self::STATUS_FISCAL;

        if ($t['status'] == self::STATUS_POSTER_OPENED) {
            $status = self::STATUS_WAITING;
            if (strpos($t['transaction_comment'], $this->configs['doneComment']) > -1) {
                $status = self::STATUS_DONE;
            }
        }
        return $status;
    }

    protected function getLastDate($t)
    {
        if ($t['date_close'] > 0) {
            return date("Y-m-d H:i:s", (int) round($t['date_close'] / 1000));
        } elseif ($t['date_start'] > 0) {
            return date("Y-m-d H:i:s", (int) round($t['date_start'] / 1000));
        }
        return false;
    }

    protected function convertedByStatus($transactions)
    {
        $result = array();
        if (isset($transactions[self::STATUS_DONE])) {
            $sorted = $this->sortTransactions($transactions[self::STATUS_DONE]);
            $result = array_merge($result, $sorted);
        }

        if (isset($transactions[self::STATUS_WAITING])) {
            $sorted = $this->sortTransactions($transactions[self::STATUS_WAITING]);
            $result = array_merge($result, $sorted);
        }
        return $result;
    }

    protected function sortTransactions($result)
    {
        usort($result, array(new Comp(), 'compare'));
        return $result;
    }

    public function getStatuses()
    {
        return array(
            'general' => array(
                self::STATUS_WAITING => "Очікування",
                self::STATUS_DONE => "Виконаний",
            ),
            'poster' => array(
                self::STATUS_POSTER_OPENED => "Відкритий",
                self::STATUS_POSTER_CLOSE => "Закритий",
                self::STATUS_POSTER_DELETED => "Видалений",

            ),
        );

    }
}

class Comp
{

    public function compare($a, $b)
    {
        if (strtotime($a['last_date']) < strtotime($b['last_date'])) {
            return 1;
        }
    }
}