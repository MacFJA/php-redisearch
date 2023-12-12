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

use function function_exists;
use function React\Async\await;

use Clue\React\Redis\Client;
use MacFJA\RediSearch\Redis\Command;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use RuntimeException;

class ReactRedisClient extends AbstractClient
{
    /**
     * @var Client
     */
    private $redis;

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $redis
     */
    private function __construct($redis)
    {
        if (!static::supports($redis)) {
            throw new RuntimeException($this->getMissingMessage('Clue Redis React and/or React\\Async', false, [
                Client::class => [],
                PromiseInterface::class => [],
            ], ['\React\Async\await']));
        }
        if ($redis instanceof Promise) {
            $realRedis = await($redis);
            if (!$realRedis instanceof Client) {
                throw new RuntimeException('The provided $redis parameter is not a valid Redis client');
            }
            $this->redis = $realRedis;

            return;
        }
        $this->redis = $redis;
    }

    /**
     * @codeCoverageIgnore
     */
    public function pipeline(Command ...$commands): array
    {
        false === static::$disableNotice
        && trigger_error('Warning, Clue\\React\\Redis\\Client don\'t use a real Redis Pipeline', E_USER_NOTICE);

        return array_map(function (Command $command) {
            return $this->execute($command);
        }, $commands);
    }

    public static function make($redis): \MacFJA\RediSearch\Redis\Client
    {
        return new self($redis);
    }

    public function executeRaw(...$args)
    {
        $command = array_shift($args);

        return await($this->redis->__call((string) $command, array_map('strval', $args)));
    }

    public static function supports($redis): bool
    {
        return ($redis instanceof Client || $redis instanceof PromiseInterface) && function_exists('\React\Async\await');
    }

    /**
     * @codeCoverageIgnore
     */
    protected function doPipeline(Command ...$commands): array
    {
        return [];
    }
}
