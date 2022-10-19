<?php

declare(strict_types=1);

/*
 * Copyright MacFJA
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MacFJA\RediSearch\Redis\Client;

use Amp\Redis\Redis as AmpRedis;
use Clue\React\Redis\Client as ReactClient;
use Credis_Client as CredisRedis;
use MacFJA\RediSearch\Redis\Client;
use Predis\ClientInterface as PredisRedis;
use React\Promise\Promise as ReactPromise;
use Redis as PhpredisRedis;
use RedisClient\Client\AbstractRedisClient as CheprasovRedis;
use redisent\Redis as RedisentRedis;
use Rediska as RediskaRedis;
use RuntimeException;

class ClientFacade
{
    /** @var array<string> */
    private $factories = [
        PredisClient::class,
        CredisClient::class,
        PhpredisClient::class,
        PhpiredisClient::class,
        CheprasovRedisClient::class,
        RedisentClient::class,
        RediskaClient::class,
        AmpRedisClient::class,
        TinyRedisClient::class,
        ReactRedisClient::class,
    ];

    /**
     * @param AmpRedis|CheprasovRedis|CredisRedis|mixed|PhpredisRedis|PredisRedis|ReactClient|ReactPromise|RedisentRedis|RediskaRedis|resource $redis
     */
    public function getClient($redis): Client
    {
        /** @var Client $factory */
        foreach ($this->factories as $factory) {
            if ($factory::supports($redis)) {
                return $factory::make($redis);
            }
        }

        throw new RuntimeException('Unable to handle the Redis connection');
    }

    public function addFactory(string $classname): bool
    {
        if (!is_subclass_of($classname, Client::class)) {
            return false;
        }
        $this->factories[] = $classname;

        return true;
    }
}
