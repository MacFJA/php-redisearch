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

use InvalidArgumentException;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\tests\fixtures\Redis\Command\FakeAbstractCommand;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\AbstractCommand
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 *
 * @internal
 */
class AbstractCommandTest extends TestCase
{
    public function testGetRediSearchVersion(): void
    {
        $command = new FakeAbstractCommand([]);

        static::assertSame('2.0.0', $command->getRediSearchVersion());
        $command->setRediSearchVersion('2.2.0');
        static::assertSame('2.2.0', $command->getRediSearchVersion());
    }

    public function testMissingRequirements(): void
    {
        $command = new FakeAbstractCommand(['req' => new NamelessOption()]);
        $command->setRequiredOptions(['req']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing command option');

        $command->getArguments();
    }

    public function testWrongOptionType(): void
    {
        $command = new FakeAbstractCommand(['req' => 10]);
        $command->setRequiredOptions(['req']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing command option');

        $command->getArguments();
    }
}
