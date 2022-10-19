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
use Predis\Command\CommandInterface;
use Predis\Command\RawCommand;
use Predis\Command\RawFactory;
use RuntimeException;

class PredisClient extends AbstractClient
{
    /** @var ClientInterface */
    private $redis;

    /** @var null|RawFactory */
    private static $rawFactory;

    /**
     * @codeCoverageIgnore
     */
    private function __construct(ClientInterface $redis)
    {
        if (!static::supports($redis)) {
            throw new RuntimeException($this->getMissingMessage('Predis', false, [
                RawCommand::class => [],
                ClientInterface::class => ['executeCommand', 'pipeline'],
                ClientContextInterface::class => ['executeCommand'],
            ]));
        }
        $this->redis = $redis;
    }

    public function execute(Command $command)
    {
        $rawResponse = $this->redis->executeCommand(self::createRawCommand(array_merge([$command->getId()], $command->getArguments())));

        return $command->parseResponse($rawResponse);
    }

    public static function supports($redis): bool
    {
        if (
            !static::fcqnExists(ClientInterface::class)
            || !static::fcqnExists(RawCommand::class)
            || !static::fcqnExists(ClientContextInterface::class)
        ) {
            return false;
        }

        return $redis instanceof ClientInterface
            && method_exists($redis, 'executeCommand')
            && method_exists($redis, 'pipeline')
            && method_exists(ClientContextInterface::class, 'executeCommand');
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
        return $this->redis->executeCommand(self::createRawCommand($args));
    }

    protected function doPipeline(Command ...$commands): array
    {
        assert(method_exists($this->redis, 'pipeline'));

        return $this->redis->pipeline(static function (ClientContextInterface $pipeline) use ($commands): void {
            foreach ($commands as $command) {
                $pipeline->executeCommand(self::createRawCommand(array_merge([$command->getId()], $command->getArguments())));
            }
        });
    }

    /**
     * @param array<float|int|string> $args
     *
     * @codeCoverageIgnore
     */
    private static function createRawCommand(array $args): CommandInterface
    {
        if (class_exists(RawFactory::class)) {
            if (null === self::$rawFactory) {
                self::$rawFactory = new RawFactory();
            }
            $commandID = array_shift($args);

            return self::$rawFactory->create((string) $commandID, $args);
        }

        // @phpstan-ignore-next-line
        return new RawCommand($args);
    }
}
