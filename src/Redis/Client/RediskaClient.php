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

use function in_array;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command;
use MacFJA\RediSearch\Redis\Initializer;
use Rediska;
use Rediska_Command_Abstract;
use Rediska_Commands;
use Rediska_Connection_Exec;
use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class RediskaClient extends AbstractClient
{
    /** @var Rediska */
    private $redis;

    private function __construct(Rediska $redis)
    {
        if (!static::supports($redis)) {
            throw new RuntimeException($this->getMissingMessage('Rediska', false, [
                Rediska::class => ['__class', 'pipeline'],
                Rediska_Command_Abstract::class => [],
                Rediska_Connection_Exec::class => [],
                Rediska_Commands::class => ['add', 'getList'],
            ]));
        }
        Initializer::registerCommandsRediska();
        $this->redis = $redis;
    }

    public static function make($redis): Client
    {
        return new self($redis);
    }

    public function execute(Command $command)
    {
        $result = $this->redis->__call('__redisearch', array_merge([$command->getId()], $command->getArguments()));

        return $command->parseResponse($result);
    }

    public function executeRaw(...$args)
    {
        $command = (string) $args[0];
        $commands = array_keys(Rediska_Commands::getList());

        if (in_array(strtolower($command), $commands, true)) {
            array_shift($args);

            return $this->redis->__call($command, $args);
        }

        return $this->redis->__call('__redisearch', $args);
    }

    public static function supports($redis): bool
    {
        return $redis instanceof Rediska
            && static::fcqnExists(Rediska_Commands::class)
            && static::fcqnExists(Rediska_Command_Abstract::class)
            && static::fcqnExists(Rediska_Connection_Exec::class)
            && method_exists($redis, '__call')
            && method_exists(Rediska_Commands::class, 'add')
            && method_exists($redis, 'pipeline');
    }

    protected function doPipeline(Command ...$commands): array
    {
        $pipeline = $this->redis->pipeline();
        foreach ($commands as $command) {
            $pipeline = $pipeline->__call('__redisearch', array_merge([$command->getId()], $command->getArguments()));
        }

        return $pipeline->execute();
    }
}
