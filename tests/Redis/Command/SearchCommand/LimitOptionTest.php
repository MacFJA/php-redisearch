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

namespace MacFJA\RediSearch\tests\Redis\Command\SearchCommand;

use MacFJA\RediSearch\Redis\Command\SearchCommand\LimitOption;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @covers \MacFJA\RediSearch\Redis\Command\SearchCommand\LimitOption
 *
 * @internal
 */
class LimitOptionTest extends \PHPUnit\Framework\TestCase
{
    public function testSetOffset(): void
    {
        $option = new LimitOption();
        $option->setSize(1);

        $option->setOffset(0);
        static::assertSame(['LIMIT', 0, 1], $option->render());

        $option->setOffset(10);
        static::assertSame(['LIMIT', 10, 1], $option->render());

        $option->setOffset(null);
        static::assertSame([], $option->render());
    }

    public function testSetSize(): void
    {
        $option = new LimitOption();
        $option->setOffset(0);

        $option->setSize(0);
        static::assertSame(['LIMIT', 0, 0], $option->render());

        $option->setSize(10);
        static::assertSame(['LIMIT', 0, 10], $option->render());

        $option->setSize(null);
        static::assertSame([], $option->render());
    }

    public function testIsValid(): void
    {
        $option = new LimitOption();
        static::assertFalse($option->isValid());

        $option->setOffset(0);
        static::assertFalse($option->isValid());

        $option->setSize(0);
        static::assertTrue($option->isValid());

        $option->setSize(-1);
        static::assertFalse($option->isValid());

        $option->setSize(10);
        $option->setOffset(-10);
        static::assertFalse($option->isValid());
    }

    public function testGetOptionData(): void
    {
        $option = new LimitOption();
        static::assertSame(['offset' => null, 'size' => null], $option->getOptionData());

        $option = new LimitOption();
        $option->setOffset(0);
        static::assertSame(['offset' => 0, 'size' => null], $option->getOptionData());

        $option = new LimitOption();
        $option->setSize(0);
        static::assertSame(['offset' => null, 'size' => 0], $option->getOptionData());

        $option = new LimitOption();
        $option->setOffset(10);
        $option->setSize(10);
        static::assertSame(['offset' => 10, 'size' => 10], $option->getOptionData());
    }
}
