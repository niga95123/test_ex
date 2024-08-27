<?php

namespace App;

use Predis\Client;

class RedisQueue
{
    protected $redis;

    public function getRedisCon() {
        return $this->redis = new Client([
            'scheme' => 'tcp',
            'host' => getenv('REDIS_HOST'),
            'port' => 6379,
        ]);
    }

}