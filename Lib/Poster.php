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
        $url = "https://api-demo.joinposter.com/api/",
        $token = "4164553abf6a031302898da7800b59fb";

    public function __construct()
    {
        $this->transport = new Curl();
    }

    public function getIncomingOrders()
    {
        $url = $this->url.'incomingOrders.getIncomingOrders?token='.$this->token;

        $orders = $this->transport->sendRequest($url);
        if ( empty($orders['response']) ){
            return [];
        }


        $result = [];
        foreach ($orders['response'] as $order ){
            $row = [
                'id' => $order['incoming_order_id'],
                'status' => $order['status'],
                'date' => date("Y-m-d H:i:s"),
            ];
            if (isset( $order['updated_at']) ){
                $row['date'] = $order['updated_at'];
            } elseif(isset($order['created_at'])){
                $row['date'] = $order['created_at'];
            }
            $result[] = $row;
        }

        return $result;
    }

}