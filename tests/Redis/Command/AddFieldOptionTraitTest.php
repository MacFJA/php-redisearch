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

use BadMethodCallException;
use MacFJA\RediSearch\tests\fixtures\Redis\Command\FakeAddFieldOptionTraitClass1;
use MacFJA\RediSearch\tests\fixtures\Redis\Command\FakeAddFieldOptionTraitClass2;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\AddFieldOptionTrait
 *
 * @uses \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\NumericFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 *
 * @internal
 */
class AddFieldOptionTraitTest extends TestCase
{
    public function testWrongParent(): void
    {
        $command = new FakeAddFieldOptionTraitClass1();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This method is not callable in '.FakeAddFieldOptionTraitClass1::class);

        $command->addNumericField('foo');
    }

    public function testWrongParent2(): void
    {
        $command = new FakeAddFieldOptionTraitClass1();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This method is not callable in '.FakeAddFieldOptionTraitClass1::class);

        $command->addJSONNumericField('$.foo', 'foo');
    }

    public function testValidParent(): void
    {
        $command = new FakeAddFieldOptionTraitClass2([]);

        $command->addNumericField('foo');

        static::assertTrue(true);
    }
}
