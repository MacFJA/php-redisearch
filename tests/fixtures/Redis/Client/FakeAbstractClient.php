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

namespace MacFJA\RediSearch\tests\fixtures\Redis\Client;

use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Command;

class FakeAbstractClient extends \MacFJA\RediSearch\Redis\Client\AbstractClient
{
    public static function make($redis): Client
    {
        return new self();
    }

    public function execute(Command $command): void
    {
        // Do nothing
    }

    public function executeRaw(...$args): void
    {
        // Do nothing
    }

    public static function supports($redis): bool
    {
        return false;
    }

    /**
     * @param array<string,array<string>> $classesMethods
     * @param array<string>               $functions
     */
    public function exposeGetMissingMessage(string $name, bool $isExtension, array $classesMethods, array $functions = []): string
    {
        return $this->getMissingMessage($name, $isExtension, $classesMethods, $functions);
    }

    protected function doPipeline(Command ...$commands): array
    {
        return [];
    }
}
