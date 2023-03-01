<?php

namespace Ozon;

class Curl
{
    // ozon
    private $host = "https://api-seller.ozon.ru";
    private $clientId = 0;
    private $apiKey = '';

    // wildberries
    private $tokenSupplier = '';
    private $tokenStatistic = '';

    public function getWB($url, $type)
    {
        if ($type === 'statistic') {
            $token = $this->tokenStatistic;
        } elseif ($type === 'supplier') {
            $token = $this->tokenSupplier;
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization:' . $token
        ];

        

        $cookie_file = 'cookieWB.txt';
        $curl = curl_init();
        $options = array(
          CURLOPT_URL => $url,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HTTPHEADER => $headers,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_SSL_VERIFYHOST => '0',
          CURLOPT_SSL_VERIFYPEER => '1',
          CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
          CURLOPT_VERBOSE        => 0,
        );
        curl_setopt_array($curl, $options);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $code = (int) $code;
        $errors = [
            301 => 'Moved permanently.',
            400 => 'Wrong structure of the array of transmitted data, or invalid identifiers of custom fields.',
            401 => 'Not Authorized. There is no account information on the server. You need to make a request to another server on the transmitted IP.',
            403 => 'The account is blocked, for repeatedly exceeding the number of requests per second.',
            404 => 'Not found.',
            500 => 'Internal server error.',
            502 => 'Bad gateway.',
            503 => 'Service unavailable.'
        ];

        if ($code < 200 || $code > 204) die( "Error $code. " . (isset($errors[$code]) ? $errors[$code] : 'Undefined error'));

        $response = json_decode($out, true);

        return $response;
    }

    public function postOzon($data, $method)
    {
        $url = 'https://api-seller.ozon.ru' . $method;
        $headers = array(
          'Content-Type: application/json',
          'Host: api-seller.ozon.ru',
            'Client-Id: ' . $this->clientId,
          'Api-Key: ' . $this->apiKey
        ) ;
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYHOST => '0',
            CURLOPT_SSL_VERIFYPEER => '1',
            CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_VERBOSE        => 0,
        );
        curl_setopt_array($ch, $options);
        $out = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        $code = (int) $code;

        $errors = [
            301 => 'Moved permanently.',
            400 => 'Wrong structure of the array of transmitted data, or invalid identifiers of custom fields.',
            401 => 'Not Authorized. There is no account information on the server. You need to make a request to another server on the transmitted IP.',
            403 => 'The account is blocked, for repeatedly exceeding the number of requests per second.',
            404 => 'Not found.',
            500 => 'Internal server error.',
            502 => 'Bad gateway.',
            503 => 'Service unavailable.'
        ];

        if ($code < 200 || $code > 204) die( "Error $code. " . (isset($errors[$code]) ? $errors[$code] : 'Undefined error'));

        $response = json_decode($out, true);

        return $response;
    }
}
