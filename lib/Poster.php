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
    const STATUS_CLOSE_DONE = 'closed_done';
    const STATUS_DELETED = 'deleted';
    const STATUS_FISCAL = 'fiscal';

    const SPOT_ID = 1;
    const SPOT_TABLE_ID = 1;

    const COUNT_ORDERS_TO_VIEW = 8;
    const HIDE_AFTER_MINUTES = 2;

    const SUCCESS_DONE_MESSAGE = "Замовлення #";


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
        $SettingsManager = new Settings();
        $configs = $SettingsManager->getSettings();
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
        $process = $this->processTransactions($response);

        if ($process['status'] == 'error' ) return $process;

        $OrdersManager = new Orders();
        $orders = $OrdersManager->getAll(array());
        $ordersByStatus = array();
        foreach ( $orders as $order ){
            $compareTime = date('Y-m-d H:i:s', strtotime('-' . self::HIDE_AFTER_MINUTES . ' minutes') );
            if ( in_array($order['status'], array(self::STATUS_DONE, self::STATUS_CLOSE_DONE) )
                && $order['last_update_date'] < $compareTime){
                    continue;
            }
            $ordersByStatus[$order['status']][] = $order;
        }
        $converted = $this->convertedByStatus($ordersByStatus, false);
        return array(
            'status' => $process['status'],
            'message' => $process['message'],
            'transactions' => array_slice($converted, 0, self::COUNT_ORDERS_TO_VIEW),
            'status_changed_to_done' => $process['changedToDone'],
        );
    }

    public function getPaginateTransactions($offset, $length)
    {
        $params = array();
        if ( $length > 0 ) $params['limit'] = $length;
        if ( $offset > 0 ) $params['offset'] = $length*$offset;

        $OrdersManager = new Orders();
        $orders = $OrdersManager->getAll($params);
        if (empty($orders)){
            return array(
                'status' => "error",
                'message' => 'Немає замовлень',
                'transactions' => array(),
            );
        }

        $resultByStatus = array();
        foreach ( $orders as $order ){
            $resultByStatus[$order['status']][] = $order;
        }
        $result = $this->convertedByStatus($resultByStatus, true);

        return array(
            'status' => 'success',
            'message' => 'Success',
            'transactions' => $result,
        );
    }

    public function getTransactionTotal()
    {
        $OrdersManager = new Orders();
        return $OrdersManager->countAll();
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

    protected function updatePosterComment($data)
    {
        $getParams = array(
            'token' => $this->configs['token'],
        );
        $postParams = array(
            'comment' => $data['comment'],
            'spot_id' => self::SPOT_ID,//todo: think about this hardcode
            'spot_tablet_id' => self::SPOT_TABLE_ID,//todo: think about this hardcode
            'transaction_id' => $data['origin_id'],
        );
        $url = $this->configs['url'].'transactions.changeComment?'.http_build_query($getParams);
        $response = $this->transport->sendRequest($url, 'post', $postParams, false);
        if ( isset($response['response'])){
            if ( isset($response['response']['err_code']) ){
                if ( $response['response']['err_code'] == 0 ){
                    return array(
                        'status' => 'success',
                        'message' => '',
                    );
                } else {
                    return array(
                        'status' => 'error',
                        'message' => 'Код помилки: '.$response['response']['err_code'],
                    );
                }
            } elseif (isset($response['error']) && isset($response['message']) ) {
                return array(
                    'status' => 'error',
                    'message' => $response['message'],
                );
            }
        } else {
            return array(
                'status' => 'error',
                'message' => 'Немає відповіді від серверу під час оновлення коментаря',
            );
        }

        return array(
            'status' => 'error',
            'message' => 'Невідома помилка під час оновлення коментаря',
       );
    }

    protected function processTransactions($response)
    {
        $changedToDone = false;
        if (empty($response)){
            return array(
                'status' => 'error',
                'message' => 'Немає відповіді від серверу',
                'changedToDone' => $changedToDone,
            );
        }

        if (isset($response['error'])){
            return array(
                'status' => 'error',
                'message' => $response['error']['message'],
                'changedToDone' => $changedToDone,
            );
        }

        $transactions = $response['response'];
        if (empty($transactions)){
            return array(
                'status' => 'success',
                'message' => 'Немає замовлень',
                'changedToDone' => $changedToDone,
            );
        }

        $OrderHistoryManager = new OrderHistory();
        $OrderHistoryManager->moveFromOrders();

        $OrdersManager = new Orders();
        $nOrders = $this->mapResponse($transactions);
        if (!$this->needDbActions($nOrders)){
            return array(
                'status' => 'waiting',
                'message' => 'Немає замовлень',
                'changedToDone' => $changedToDone,
            );
        }

        $exists = array();
        $existOrders = array();
        if (!empty($nOrders)){
            $exists = $OrdersManager->getListByOriginIds(array_column($nOrders, 'origin_id'));
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
            function aUkSort($a, $b) {
                return ($a['last_date'] < $b['last_date']) ? -1 : 1;
            }
            uksort($nOrders, 'aUkSort');
        }

        $i = $maxId = $OrdersManager->getMaxId();

        foreach ( $nOrders as $originId => $order ){
            ++$i;
            $nOrders[$originId]['view_id'] = $i;

            //update comment in POSTER
            if ( $order['status'] != self::STATUS_WAITING ) {
                continue;
            }
            if (strpos($order['comment'], self::SUCCESS_DONE_MESSAGE) > -1) {
                continue;
            }

            $nOrders[$originId]['comment'] = self::SUCCESS_DONE_MESSAGE."$i \n".$nOrders[$originId]['comment'];
            $updateCommentResponse = $this->updatePosterComment($nOrders[$originId]);
            if ($updateCommentResponse['status'] == 'error'){
                return array(
                    'status' => 'error',
                    'message' => $updateCommentResponse['message'],
                    'changedToDone' => $changedToDone,
                );
            }
        }

        $db = new DB();
        $db->beginTransaction();

        //create new orders
        if (!empty($nOrders)) {
            if (!$OrdersManager->createList($nOrders)) {
                $db->rollBack();
                return array(
                    'status' => 'error',
                    'message' => 'Помилка під час запису нових чеків',
                    'changedToDone' => $changedToDone,
                );
            }
        }

        //update orders
        if (!empty($existOrders)){
            $params['filters'] = array('origin_id' => 'IN ( '.implode(",", array_keys($existOrders) ).' )');
            $existInDbOrders = $OrdersManager->getAll($params);
            foreach ( $existInDbOrders as $eOrder ){
                $needUpdate = false;
                $originId = $eOrder['origin_id'] ;
                if ( $eOrder['origin_status'] != $existOrders[$originId]['origin_status'] ){
                    $needUpdate = true;
                }

                if ( $eOrder['status'] != $existOrders[$originId]['status']  ){
                    $needUpdate = true;
                    if ( in_array($existOrders[$originId]['status'], array(self::STATUS_DONE, self::STATUS_CLOSE_DONE) )){
                        $changedToDone = true;
                    }
                }

                if ( $eOrder['comment'] != $existOrders[$originId]['comment']  ){
                    $needUpdate = true;
                }

                //update
                if ( $needUpdate ){
                    if (!$OrdersManager->updateStatus($existOrders[$originId])){
                        $db->rollBack();
                        return array(
                            'status' => 'error',
                            'message' => 'Помилка під час оновлення чеків',
                            'changedToDone' => $changedToDone,
                        );
                    }
                }
            }
        }
        $db->executeTransaction();

        return array(
            'status' => 'success',
            'message' => 'Success',
            'changedToDone' => $changedToDone,
        );
    }

    protected function needDbActions($response)
    {
        $OrdersManager = new Orders();
        $dbOrdersSource = $OrdersManager->getAll();
        if (count($dbOrdersSource) != count($response)){
            return true;
        }

        $dbOrders = array();
        foreach ($dbOrdersSource as $o ){
            $dbOrders[$o['origin_id']] = $o['origin_id'].";"
                .$o['origin_status'].";"
                .$o['comment'].";"
                .$o['last_date'];
        }
        sort($dbOrders);
        $dbCheckSum = md5(implode(",", $dbOrders));

        $orders = array();
        foreach ($response as $r ){
            $orders[] = $o['origin_id'].";"
                .$o['origin_status'].";"
                .$o['comment'].";"
                .$o['last_date'];
        }
        sort($orders);
        $checkSum = md5(implode(",", $orders));

        if ($checkSum == $dbCheckSum){
            return false;
        }
        return true;
    }

    protected function mapResponse($transactions)
    {
        $orders = array();
        foreach ($transactions as $k => $t) {
            $order = array(
                'origin_id' => $t['transaction_id'],
                'view_id' => null,
                'status' => $this->getInnerStatus($t),
                'origin_status' => $t['status'],
                'comment' => (isset ($t['transaction_comment']) ? $t['transaction_comment'] : null ),
                'last_date' => $this->getLastDate($t),
            );

            if ( empty($order['last_date']) OR $order['last_date'] < date("Y-m-d 00:00:00") ) continue;

            $orders[$t['transaction_id']] = $order;
        }
        return $orders;
    }

    protected function getInnerStatus($t)
    {
        if ($t['status'] == self::STATUS_POSTER_DELETED) return self::STATUS_DELETED;
        if ($t['status'] == self::STATUS_POSTER_FISCAL) return self::STATUS_FISCAL;

        if ($t['status'] == self::STATUS_POSTER_CLOSE) {
            $status = self::STATUS_CLOSE;
            if ( strpos($t['transaction_comment'], $this->configs['doneComment']) > -1 ) {
                $status = self::STATUS_CLOSE_DONE;
            }
        }

        if ($t['status'] == self::STATUS_POSTER_OPENED) {
            $status = self::STATUS_WAITING;
            if ( strpos($t['transaction_comment'], $this->configs['doneComment']) > -1 ) {
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

    protected function convertedByStatus($transactions, $full = true)
    {
        $result = array();

        //priority
        $gStatuses = $this->getStatuses();
        $statuses = array_keys($gStatuses['inner']);
        if ( !$full ){
            $statuses = array(
                self::STATUS_WAITING,
                self::STATUS_DONE,
                self::STATUS_CLOSE_DONE,
            );
        }
        foreach ( $statuses as $statusKey ){
            if (isset($transactions[$statusKey])) {
                $sorted = $this->sortTransactions($transactions[$statusKey]);
                $result = array_merge($result, $sorted);
            }
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
            'inner' => array(
                self::STATUS_WAITING => "Очікування",
                self::STATUS_DONE => "Виконаний",
                self::STATUS_CLOSE => "Закритий",
                self::STATUS_DELETED => "Видалений",
                self::STATUS_FISCAL => "Фіскальний",
            ),
            'poster' => array(
                self::STATUS_POSTER_OPENED => "Відкритий",
                self::STATUS_POSTER_CLOSE => "Закритий",
                self::STATUS_POSTER_DELETED => "Видалений",
                self::STATUS_POSTER_FISCAL => "Фіскальний",
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