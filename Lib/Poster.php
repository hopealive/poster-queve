<?php
/**
 * Description of Poster
 *
 * @author gregzorb
 */
require_once('Lib/Curl.php');

class Poster
{
    private $transport,
        $url = "https://gregzorb.joinposter.com/api/",
        $token = "0690289d0f354269eda7a6537a9cec6b";

    public $transactionStatuses = [
        1 => "открыт",
        2 => "закрыт",
        3 => "удален",
    ];

    public function __construct()
    {
        date_default_timezone_set('UTC');
        $this->transport = new Curl();
    }

    public function getLastTransactions()
    {
        $yesterday = date("Ymd", time() - 60 * 60 * 24);
        $url = $this->url.'dash.getTransactions?include_products=false&dateFrom='.$yesterday.'&token='.$this->token;

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