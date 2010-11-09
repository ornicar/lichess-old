<?php

namespace Bundle\LichessBundle\OpenId;

class Janrain
{
    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function connect($token)
    {
        if(empty($token)) {
            throw new \InvalidArgumentException('Janrain: empty token');
        }

        $data = array('token' => $token, 'apiKey' => $this->apiKey, 'format' => 'json');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $json = curl_exec($curl);
        curl_close($curl);

        $connection = json_decode($json, true);

        if(empty($connection)) {
            throw new \RuntimeException('Janrain: bad response from server = '.$json);
        }

        if('ok' != $connection['stat']) {
            throw new \RuntimeException('Janrain: connection failed = '.var_export($connection, true));
        }

        return $connection['profile'];
    }
}
