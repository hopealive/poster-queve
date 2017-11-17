<?php
/**
 * Description of Poster
 *
 * @author gregzorb
 */
require_once('Lib/Curl.php');
require_once('config.php');

class Poster
{
    private $transport,
        $configs;


    public $transactionStatuses = [
        1 => "открыт",
        2 => "закрыт",
        3 => "удален",
    ];

    public function __construct()
    {
        date_default_timezone_set('UTC');
        $this->transport = new Curl();
        $this->configs = (new Config())->configs;
    }

    public function getLastTransactions()
    {
        $yesterday = date("Ymd", time() - 60 * 60 * 24);
        
        $params = [
            'include_products' => false,
            'dateFrom' => $yesterday,
            'token' => $this->configs['token'],
        ];

        $url = $this->configs['url'].'dash.getTransactions?'.http_build_query($params);

        $transactions = $this->transport->sendRequest($url);
        if ( empty($transactions['response']) ){
            return [];
        }

        $result = [];
        foreach ($transactions['response'] as $transaction ){
            $row = [
                'id' => $transaction['transaction_id'],
                'status' => $transaction['status'],
                'last_date' => date("Y-m-d H:i:s"),
            ];

            $date = new DateTime();
            if ( $transaction['date_close'] > 0 ){
                $date->setTimestamp(round($transaction['date_close']/1000));
                $row['last_date'] = $date->format("Y-m-d H:i:s");
            } elseif( $transaction['date_start'] > 0 ){
                $date->setTimestamp(round($transaction['date_start']/1000));
                $row['last_date'] = $date->format("Y-m-d H:i:s");
            }
            $result[] = $row;
        }

        return $result;
    }

}