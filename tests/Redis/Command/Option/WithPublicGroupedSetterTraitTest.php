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

use BadMethodCallException;
use InvalidArgumentException;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\tests\fixtures\Redis\Command\Option\FakeWithPublicGroupedSetter;
use MacFJA\RediSearch\tests\fixtures\Redis\Command\Option\FakeWithPublicGroupedSetterInvalidSelf;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\Option\WithPublicGroupedSetterTrait
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 *
 * @internal
 */
class WithPublicGroupedSetterTraitTest extends TestCase
{
    public function testInvalidSelf(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This method is not callable in '.FakeWithPublicGroupedSetterInvalidSelf::class);
        $class = new FakeWithPublicGroupedSetterInvalidSelf();
        $class->setName('John');
    }

    public function testInvalidSetterName(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call undefined method setAge in '.FakeWithPublicGroupedSetter::class);
        $class = new FakeWithPublicGroupedSetter([], []);
        $class->setAge(50);
    }

    public function testInvalidSetterArgCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The method '.FakeWithPublicGroupedSetter::class.'::setName need exactly one argument');
        $class = new FakeWithPublicGroupedSetter([], []);
        $class->setName('John', 'Doe');
    }

    public function testUndefinedMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call undefined method foo in '.FakeWithPublicGroupedSetter::class);
        $class = new FakeWithPublicGroupedSetter([], []);
        $class->foo();
    }

    public function testSetter(): void
    {
        $class = new FakeWithPublicGroupedSetter(['name' => new NamelessOption()], []);
        $class->setName('John');
        static::assertSame('John', $class->getDataOfOption('name'));
    }
}
