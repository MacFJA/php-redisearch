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
use function is_resource;

use Exception;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command;
use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class PhpiredisClient extends AbstractClient
{
    /** @var resource */
    private $redis;

    /**
     * @param resource $redis
     *
     * @psalm-assert function_exists('phpiredis_command_bs')
     * @psalm-assert function_exists('phpiredis_multi_command_bs')
     */
    private function __construct($redis)
    {
        if (!static::supports($redis)) {
            throw new RuntimeException($this->getMissingMessage('phpiredis', true, [], ['phpiredis_command_bs', 'phpiredis_multi_command_bs']));
        }
        $this->redis = $redis;
    }

    public function execute(Command $command)
    {
        $rawResponse = phpiredis_command_bs($this->redis, array_merge([$command->getId()], $command->getArguments()));

        return $command->parseResponse($rawResponse);
    }

    public static function supports($redis): bool
    {
        if (!is_resource($redis)
            || !function_exists('phpiredis_command_bs')
            || !function_exists('phpiredis_multi_command_bs')
        ) {
            return false;
        }

        try {
            $response = (string) phpiredis_command_bs($redis, ['PING']);

            return 'pong' === strtolower($response);
        } catch (Exception $exception) {
            return false;
        }
    }

    public static function make($redis): Client
    {
        return new self($redis);
    }

    public function executeRaw(...$args)
    {
        $args = array_map('strval', $args);

        return phpiredis_command_bs($this->redis, $args);
    }

    protected function doPipeline(Command ...$commands): array
    {
        return phpiredis_multi_command_bs($this->redis, array_map(static function (Command $command) {
            return array_merge([$command->getId()], $command->getArguments());
        }, $commands));
    }
}
