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

use MacFJA\RediSearch\Redis\Command\CursorRead;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @covers \MacFJA\RediSearch\Redis\Command\CursorRead
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
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
}
