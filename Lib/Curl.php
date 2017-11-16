<?php

/**
 * Description of Curl
 *
 * @author gregzorb
 */
class Curl
{

    public function sendRequest($url, $type = 'get', $params = [], $json = false)
    {
        $ch = curl_init();
        if (false == $ch) {
            throw new \HttpException('cannot create cURL handle');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($type == 'post' || $type == 'put') {
            curl_setopt($ch, CURLOPT_POST, true);

            if ($json) {
                $params = json_encode($params);

                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    [
                    'Content-Type: application/json',
                    'Content-Length: '.strlen($params)
                ]);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Poster (http://joinposter.com)');

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if (false === $result || $info['http_code'] != 200) {
            die("Cannot fetch data using cURL: $url. Data: ".json_encode($info));
        }

        return json_decode($result, 1);
    }
}