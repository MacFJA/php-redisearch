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

use MacFJA\RediSearch\Redis\Command\Config;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @covers \MacFJA\RediSearch\Redis\Command\Config
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 *
 * @internal
 */
class ConfigTest extends TestCase
{
    public function testGetId(): void
    {
        $command = new Config();
        static::assertSame('FT.CONFIG', $command->getId());
    }

    public function testGetAll(): void
    {
        $command = new Config();
        $command->setGetMode();
        static::assertSame(['GET', '*'], $command->getArguments());
    }

    public function testGetOne(): void
    {
        $command = new Config();
        $command->setGetMode('foo');
        static::assertSame(['GET', 'foo'], $command->getArguments());
    }

    public function testHelpAll(): void
    {
        $command = new Config();
        $command->setHelpMode();
        static::assertSame(['HELP', '*'], $command->getArguments());
    }

    public function testHelpOne(): void
    {
        $command = new Config();
        $command->setHelpMode('foo');
        static::assertSame(['HELP', 'foo'], $command->getArguments());
    }
}
