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

namespace MacFJA\RediSearch\tests\Redis\Command;

use MacFJA\RediSearch\Exception\UnexpectedServerResponseException;
use MacFJA\RediSearch\Redis\Command\CursorRead;
use MacFJA\RediSearch\Redis\Response\CursorResponse;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @covers \MacFJA\RediSearch\Redis\Command\CursorRead
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Response\CursorResponse
 * @uses \MacFJA\RediSearch\Exception\UnexpectedServerResponseException
 *
 * @internal
 */
class CursorReadTest extends TestCase
{
    public function testGetId(): void
    {
        $command = new CursorRead();
        static::assertSame('FT.CURSOR', $command->getId());
    }

    public function testCountOption(): void
    {
        $command = new CursorRead();
        $command->setIndex('idx');
        $command->setCursorId(398);
        $command->setCount(30);
        static::assertSame(['READ', 'idx', 398, 'COUNT', 30], $command->getArguments());

        $command->setNoCount();
        static::assertSame(['READ', 'idx', 398], $command->getArguments());
    }

    public function testFullOption(): void
    {
        $command = new CursorRead();
        $command->setIndex('idx');
        $command->setCursorId(398);
        $command->setCount(30);
        static::assertSame(['READ', 'idx', 398, 'COUNT', 30], $command->getArguments());
    }

    public function testParseResponseWithError(): void
    {
        $this->expectException(UnexpectedServerResponseException::class);

        $command = new CursorRead();
        $command->parseResponse('');
    }

    public function testParseResponse(): void
    {
        $rawResponse = [
            0 => [
                0 => 0,
                1 => [
                    0 => 'f1',
                    1 => 'bar',
                ],
                2 => [
                    0 => 'f1',
                    1 => 'baz',
                ],
            ],
            1 => 976015094,
        ];
        $command = new CursorRead();
        $command->setIndex('idx')
            ->setCursorId(976015094)
        ;
        $response = $command->parseResponse($rawResponse);

        static::assertInstanceOf(CursorResponse::class, $response);
    }
}
