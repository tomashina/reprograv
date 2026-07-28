<?php

namespace Agmedia\Api;

use Agmedia\Helpers\Log;

/**
 * Class Luceed
 * @package Agmedia\Luceed
 */
class Api
{

    /**
     * @var string|mixed
     */
    private $username;

    /**
     * @var string|mixed
     */
    private $password;

    /**
     * @var string|mixed
     */
    private $token;

    /**
     * @var string|mixed
     */
    private $url;


    /**
     * @param string $username
     * @param string $pass
     * @param string $token
     * @param string $url
     */
    public function __construct()
    {
        $this->username = agconf('import.api.username');
        $this->password = agconf('import.api.password');
        $this->token    = agconf('import.api.token');
        $this->url      = agconf('import.api.url');
    }


    /**
     * @param string     $url
     * @param string|null $data
     *
     * @return bool|string
     */
    public function get(string $url, string $data = null)
    {
        try {
            $ch = curl_init($this->url . $url);
            curl_setopt($ch, CURLOPT_HEADER, ("Content-Type: application/json"));
            curl_setopt($ch, CURLOPT_USERPWD, $this->resolveApiPassword());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);

            if ($data !== null || $data !== '') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }

            $response = curl_exec($ch);
            curl_close($ch);

            $this->log($url, '');
            $this->log($response, '');

            return $this->resolveResponse($response);

        } catch (\Exception $exception) {
            $this->log($url, $exception);

            return false;
        }
    }


    /**
     * @param string $url
     * @param string $body
     * @param string $headers_type
     *
     * @return false|mixed
     */
    public function post(string $url, string $body, string $headers_type = 'form')
    {
        try {
            $ch = curl_init($this->url . $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_USERPWD, $this->resolveApiPassword());
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->resolveHeaders($headers_type));

            $response = curl_exec($ch);

            $this->log($url, $body);
            $this->log($url, $response);

            if (curl_errno($ch)) {
                $this->log($url, curl_error($ch));

                return false;
            }

            curl_close($ch);

            return $this->resolveResponse($response);

        } catch (\Exception $exception) {
            $this->log($url, $exception);

            return false;
        }
    }


    /**
     * @param string $sku
     *
     * @return string
     */
    public function resolveImageData(string $sku): string
    {
        return 'productCode=' . $sku;
    }

    /*******************************************************************************
     *                                Copyright : AGmedia                           *
     *                              email: filip@agmedia.hr                         *
     *******************************************************************************/

    private function resolveResponse($response)
    {
        $response = json_decode($response, true);

        if (isset($response['response']['result'])) {
            $response = $response['response']['result'];
        }

        return $response;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function resolveApiPassword(): string
    {
        return $this->username . ':' . $this->token . '_' . $this->password;
    }


    /**
     * @param string $type
     *
     * @return array
     */
    private function resolveHeaders(string $type): array
    {
        $headers = [];

        if ($type == 'form') {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        if ($type == 'xml') {
            $headers[] = 'Content-Type: application/xml';
        }

        return $headers;
    }


    /**
     * @param string          $type
     * @param string          $url
     * @param \Exception|null $exception
     *
     * @return void
     */
    private function log(string $type, string $url, \Exception $exception = null): void
    {
        $log_name = 'eracuni_' . $type;

        Log::store($url, $log_name);

        if ($exception) {
            Log::store($exception->getMessage(), $log_name);
        }
    }
}