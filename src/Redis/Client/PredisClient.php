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

use function assert;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command;
use Predis\ClientContextInterface;
use Predis\ClientInterface;
use Predis\Command\RawCommand;
use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class PredisClient implements Client
{
    /** @var ClientInterface */
    private $redis;

    private function __construct(ClientInterface $redis)
    {
        if (
            !static::fcqnExists(\Predis\Command\Command::class)
            || !static::fcqnExists(ClientContextInterface::class)
            || !method_exists(ClientInterface::class, 'executeCommand')
            || !method_exists($redis, 'pipeline')
            || !method_exists(ClientContextInterface::class, 'executeCommand')
        ) {
            throw new RuntimeException(
                'Dependency Predis is missing.'.PHP_EOL.
                'Install the dependency or use a polyfill that provide the classes'.
                ' "\\Predis\\Command\\Command" and "\\Predis\\ClientInterface" and "\\Predis\\ClientContextInterface"'.
                ' and functions "\\Predis\\ClientInterface::executeCommand" and "\\Predis\\ClientInterface::pipeline"'.
                ' and "\\Predis\\ClientContextInterface::executeCommand"'
            );
        }
        $this->redis = $redis;
    }

    public function execute(Command $command)
    {
        $rawResponse = $this->redis->executeCommand(new RawCommand(array_merge([$command->getId()], $command->getArguments())));

        return $command->parseResponse($rawResponse);
    }

    public static function supports($redis): bool
    {
        if (
            !static::fcqnExists(ClientInterface::class)
            || !static::fcqnExists(RawCommand::class)
        ) {
            return false;
        }

        return $redis instanceof ClientInterface
            && method_exists($redis, 'executeCommand')
            && method_exists($redis, 'pipeline');
    }

    public static function make($redis): Client
    {
        return new self($redis);
    }

    /**
     * @inheridoc
     */
    public function executeRaw(...$args)
    {
        return $this->redis->executeCommand(new RawCommand($args));
    }

    public function pipeline(Command ...$commands): array
    {
        assert(method_exists($this->redis, 'pipeline'));
        $results = $this->redis->pipeline(static function (ClientContextInterface $pipeline) use ($commands): void {
            foreach ($commands as $command) {
                $pipeline->executeCommand(new RawCommand(array_merge([$command->getId()], $command->getArguments())));
            }
        });

        return array_map(static function ($result, $index) use ($commands) {
            return $commands[$index]->parseResponse($result);
        }, $results, array_keys($results));
    }

    private static function fcqnExists(string $fcqn): bool
    {
        return class_exists($fcqn) || interface_exists($fcqn);
    }
}
