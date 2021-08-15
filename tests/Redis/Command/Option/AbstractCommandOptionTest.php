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

namespace MacFJA\RediSearch\tests\Redis\Command\Option;

use MacFJA\RediSearch\tests\fixtures\Redis\Command\Option\FakeAbstractCommandOption;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 *
 * @internal
 */
class AbstractCommandOptionTest extends \PHPUnit\Framework\TestCase
{
    public function testIsCompatible(): void
    {
        $noConstraint = new FakeAbstractCommandOption();
        static::assertTrue($noConstraint->isCompatible(null));
        static::assertFalse($noConstraint->isCompatible('foo'));
        static::assertTrue($noConstraint->isCompatible('1.0.0'));
        static::assertTrue($noConstraint->isCompatible('1.5.0'));
        static::assertTrue($noConstraint->isCompatible('2.0.0'));
        static::assertTrue($noConstraint->isCompatible('2.5.0'));

        $fixConstraint = new FakeAbstractCommandOption('1.5.0');
        static::assertTrue($fixConstraint->isCompatible(null));
        static::assertFalse($fixConstraint->isCompatible('foo'));
        static::assertFalse($fixConstraint->isCompatible('1.0.0'));
        static::assertTrue($fixConstraint->isCompatible('1.5.0'));
        static::assertFalse($fixConstraint->isCompatible('2.0.0'));
        static::assertFalse($fixConstraint->isCompatible('2.5.0'));

        $simpleInterval = new FakeAbstractCommandOption('^1.1.0');
        static::assertTrue($simpleInterval->isCompatible(null));
        static::assertFalse($simpleInterval->isCompatible('foo'));
        static::assertFalse($simpleInterval->isCompatible('1.0.0'));
        static::assertTrue($simpleInterval->isCompatible('1.5.0'));
        static::assertFalse($simpleInterval->isCompatible('2.0.0'));
        static::assertFalse($simpleInterval->isCompatible('2.5.0'));

        $simpleInterval2 = new FakeAbstractCommandOption('>=1.1.0');
        static::assertTrue($simpleInterval2->isCompatible(null));
        static::assertFalse($simpleInterval2->isCompatible('foo'));
        static::assertFalse($simpleInterval2->isCompatible('1.0.0'));
        static::assertTrue($simpleInterval2->isCompatible('1.5.0'));
        static::assertTrue($simpleInterval2->isCompatible('2.0.0'));
        static::assertTrue($simpleInterval2->isCompatible('2.5.0'));

        $multiInterval = new FakeAbstractCommandOption('>=1.1.0 <=2.4');
        static::assertTrue($multiInterval->isCompatible(null));
        static::assertFalse($multiInterval->isCompatible('foo'));
        static::assertFalse($multiInterval->isCompatible('1.0.0'));
        static::assertTrue($multiInterval->isCompatible('1.5.0'));
        static::assertTrue($multiInterval->isCompatible('2.0.0'));
        static::assertFalse($multiInterval->isCompatible('2.5.0'));

        /** @var FakeAbstractCommandOption $invalidConstraint */
        $invalidConstraint = new FakeAbstractCommandOption('bar');
        static::assertTrue($invalidConstraint->isCompatible(null));
        static::assertFalse($invalidConstraint->isCompatible('foo'));
        static::assertFalse($invalidConstraint->isCompatible('1.0.0'));
        static::assertFalse($invalidConstraint->isCompatible('1.5.0'));
        static::assertFalse($invalidConstraint->isCompatible('2.0.0'));
        static::assertFalse($invalidConstraint->isCompatible('2.5.0'));
    }

    public function testGetVersionConstraint(): void
    {
        $noConstraint = new FakeAbstractCommandOption();
        static::assertSame('*', $noConstraint->getVersionConstraint());

        $fixConstraint = new FakeAbstractCommandOption('1.5.0');
        static::assertSame('1.5.0', $fixConstraint->getVersionConstraint());

        $simpleInterval = new FakeAbstractCommandOption('^1.1.0');
        static::assertSame('^1.1.0', $simpleInterval->getVersionConstraint());
    }
}
