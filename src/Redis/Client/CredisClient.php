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

use function is_array;

use Credis_Client;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command;
use RuntimeException;

class CredisClient extends AbstractClient
{
    /** @var Credis_Client */
    private $redis;

    /**
     * @codeCoverageIgnore
     */
    private function __construct(Credis_Client $redis)
    {
        if (!self::supports($redis)) {
            throw new RuntimeException($this->getMissingMessage('Credis', false, [
                Credis_Client::class => ['__call'],
            ]));
        }
        $this->redis = $redis;
    }

    public static function make($redis): Client
    {
        return new self($redis);
    }

    public function executeRaw(...$args)
    {
        $command = array_shift($args);

        return $this->fixFalseToNull($this->redis->__call($command, $args));
    }

    public static function supports($redis): bool
    {
        return $redis instanceof Credis_Client
            && method_exists($redis, '__call');
    }

    protected function doPipeline(Command ...$commands): array
    {
        $pipeline = $this->redis->pipeline();
        foreach ($commands as $command) {
            $pipeline = $pipeline->__call($command->getId(), $command->getArguments());
        }

        return $this->fixFalseToNull($pipeline->exec());
    }

    /**
     * @param false|mixed $response
     *
     * @return null|mixed
     */
    private function fixFalseToNull($response)
    {
        if (false === $response) {
            return null;
        }
        if (is_array($response)) {
            foreach ($response as &$item) {
                $item = $this->fixFalseToNull($item);
            }
        }

        return $response;
    }
}
