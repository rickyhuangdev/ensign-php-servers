<?php

namespace Rickytech\Library\Services\Helpers;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

class GuzzleHttpRequest
{
    /**
     * Get Request
     * Created by rickyhuang
     * @param string $url
     * @param array $options
     * @return mixed|null
     * @throws JsonException
     */
    public static function get(string $url, array $options = [])
    {
        return self::sendRequest($url, 'GET', $options);
    }

    public static function post(string $url, array $options = [], bool $isJson = true)
    {
        if ($isJson) {
            $options['json'] = $options['data'];
        } else {
            $options['form_params'] = $options['data'];
        }
        unset($options['data']);
        return self::sendRequest($url, 'POST', $options);
    }

    /**
     * PUT Request
     * Created by rickyhuang
     * @param string $url
     * @param array $options
     * @return mixed|null
     * @throws GuzzleException
     * @throws JsonException
     */
    public static function put(string $url, array $options = [])
    {
        return self::sendRequest($url, 'PUT', $options);
    }

    /**
     * Delete Request
     * Created by rickyhuang
     * @param string $url
     * @param array $options
     * @return mixed|null
     * @throws GuzzleException
     * @throws JsonException
     */
    public static function delete(string $url, array $options = [])
    {
        return self::sendRequest($url, 'PUT', $options);
    }

    /**
     * Created by rickyhuang
     * @param string $url
     * @param string $method
     * @param array $options
     * @return mixed|void
     * @throws GuzzleException
     * @throws JsonException
     */
    public static function sendRequest(string $url, string $method, array $options = [])
    {
        try {
            $options = self::getOptions($options);
            $client = new GuzzleClient($options);
            $response = $client->request($method, $url, $options);
            return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            }
        }
    }

    public static function getOptions($params): array
    {
        $baseSettings = [
            'connect_timeout' => 10,
            'timeout' => 10,
            'verify' => false,
            'debug' => false,
        ];
        return array_merge($baseSettings, $params);
    }

    public static function download(string $url, string $method = 'GET', array $options = [])
    {
        try {
            $options = self::getOptions($options);
            $client = new GuzzleClient($options);
            $response = $client->request($method, $url, $options);
            return $response->getBody();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            }
        }
    }
}
