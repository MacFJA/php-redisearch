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

use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command;
use RuntimeException;

class TinyRedisClient extends AbstractClient
{
    /** @var \TinyRedisClient */
    private $redis;

    public function __construct(\TinyRedisClient $redis)
    {
        if (!self::supports($redis)) {
            throw new RuntimeException($this->getMissingMessage(
                'TinyRedis',
                false,
                [\TinyRedisClient::class => ['__call']]
            ));
        }
        $this->redis = $redis;
    }

    public function pipeline(Command ...$commands): array
    {
        false === static::$disableNotice
            && trigger_error('Warning, \\TinyRedisClient don\'t use a real Redis Pipeline', E_USER_NOTICE);

        return array_map(function (Command $command) {
            return $this->execute($command);
        }, $commands);
    }

    public function execute(Command $command)
    {
        $result = $this->redis->__call($command->getId(), $command->getArguments());

        return $command->parseResponse($result);
    }

    public function executeRaw(...$args)
    {
        $command = array_shift($args);

        return $this->redis->__call($command, $args);
    }

    public static function supports($redis): bool
    {
        return $redis instanceof \TinyRedisClient
            && method_exists($redis, '__call');
    }

    public static function make($redis): Client
    {
        return new self($redis);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function doPipeline(Command ...$commands): array
    {
        return [];
    }
}
