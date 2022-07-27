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

use function count;

use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command;
use Redis;
use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class PhpredisClient extends AbstractClient
{
    /** @var Redis */
    private $redis;

    private function __construct(Redis $redis)
    {
        if (!static::supports($redis)) {
            throw new RuntimeException($this->getMissingMessage('phpredis', true, [
                Redis::class => ['rawCommand', 'multi', 'exec', 'getOption', 'setOption'],
            ]));
        }
        $this->redis = $redis;
    }

    public function execute(Command $command)
    {
        $arguments = $command->getArguments();

        $originalValue = $this->redis->getOption(Redis::OPT_REPLY_LITERAL) ?? false;
        $this->redis->setOption(Redis::OPT_REPLY_LITERAL, true);

        if (0 === count($arguments)) {
            /** @psalm-suppress TooFewArguments */
            $rawResponse = $this->redis->rawCommand($command->getId());
        } else {
            $rawResponse = $this->redis->rawCommand($command->getId(), ...$arguments);
        }

        $this->redis->setOption(Redis::OPT_REPLY_LITERAL, $originalValue);

        return $command->parseResponse($rawResponse);
    }

    public static function supports($redis): bool
    {
        return $redis instanceof Redis
            && method_exists($redis, 'rawCommand')
            && method_exists($redis, 'multi')
            && method_exists($redis, 'setOption')
            && method_exists($redis, 'getOption')
            && method_exists($redis, 'exec');
    }

    public static function make($redis): Client
    {
        return new self($redis);
    }

    public function executeRaw(...$args)
    {
        if (count($args) < 1) {
            return null;
        }
        // @phpstan-ignore-next-line
        return $this->redis->rawCommand(...$args);
    }

    protected function doPipeline(Command ...$commands): array
    {
        $pipeline = $this->redis->multi();
        foreach ($commands as $command) {
            $arguments = $command->getArguments();
            if (0 === count($arguments)) {
                $arguments = [null];
            }
            $pipeline = $pipeline->rawCommand($command->getId(), ...$arguments);
        }

        return $pipeline->exec();
    }
}
