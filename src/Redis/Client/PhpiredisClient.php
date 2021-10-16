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

use Exception;
use function function_exists;
use function is_resource;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command;
use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class PhpiredisClient implements Client
{
    /** @var resource */
    private $redis;

    /**
     * @param resource $redis
     */
    private function __construct($redis)
    {
        $this->validateEnvironment();
        $this->redis = $redis;
    }

    public function execute(Command $command)
    {
        $this->validateEnvironment();
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
        $this->validateEnvironment();

        $args = array_map('strval', $args);

        return phpiredis_command_bs($this->redis, $args);
    }

    public function pipeline(Command ...$commands): array
    {
        $results = phpiredis_multi_command_bs($this->redis, array_map(static function (Command $command) {
            return array_merge([$command->getId()], $command->getArguments());
        }, $commands));

        return array_map(static function ($result, $index) use ($commands) {
            return $commands[$index]->parseResponse($result);
        }, $results, array_keys($results));
    }

    /**
     * @psalm-assert function_exists('phpiredis_command')
     */
    private function validateEnvironment(): void
    {
        if (!function_exists('phpiredis_command_bs') || !function_exists('phpiredis_multi_command_bs')) {
            throw new RuntimeException(
                'The extension phpiredis is missing.'.PHP_EOL.
                'Install the extension or use a polyfill that provide the functions "phpiredis_command_bs" and "phpiredis_multi_command_bs"'
            );
        }
    }
}
