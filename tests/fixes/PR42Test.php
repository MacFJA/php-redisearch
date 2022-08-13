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

namespace MacFJA\RediSearch\tests\fixes;

use Exception;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Initializer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Initializer
 *
 * @internal
 */
class PR42Test extends TestCase
{
    public function testUnavailableCommand(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('executeRaw')->willThrowException(new Exception('ERR unknown command `module`, with args beginning with: `list`'));

        $response = Initializer::getRediSearchVersion($client);

        static::assertNull($response);
    }

    public function testUnknownError(): void
    {
        $this->expectException(Exception::class);

        $client = $this->createMock(Client::class);
        $client->method('executeRaw')->willThrowException(new Exception('ERR unknown server error'));

        Initializer::getRediSearchVersion($client);
    }
}
