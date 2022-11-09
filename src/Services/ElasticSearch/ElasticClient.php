<?php

namespace Rickytech\Library\Services\ElasticSearch;

use Hyperf\Guzzle\RingPHP\PoolHandler;
use Swoole\Coroutine;

class ElasticClient
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private static function initElasticClient()
    {
        $builder = \Elasticsearch\ClientBuilder::create();
        if (Coroutine::getCid() > 0) {
            $handler = make(PoolHandler::class, [
                'option' => [
                    'max_connections' => 50,
                ],
            ]);
            $builder->setHandler($handler);
        }
        return $builder->setHosts([env('EL_HOST')])->build();
    }

    public static function getElasticClient()
    {
        return self::initElasticClient();
    }
}
