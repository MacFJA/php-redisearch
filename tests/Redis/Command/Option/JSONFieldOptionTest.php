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

use MacFJA\RediSearch\Exception\InvalidJSONPathException;
use MacFJA\RediSearch\Redis\Command\CreateCommand\JSONFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\CreateCommand\JSONFieldOption
 *
 * @uses \MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\GroupedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Exception\InvalidJSONPathException
 *
 * @internal
 */
class JSONFieldOptionTest extends TestCase
{
    public function testIsValid(): void
    {
        $option = new TextFieldOption();
        $json = new JSONFieldOption('$.data', $option);

        static::assertFalse($option->isValid());
        static::assertFalse($json->isValid());

        $option->setField('data');
        static::assertTrue($option->isValid());

        try {
            new JSONFieldOption('', $option);
            static::fail();
        } catch (InvalidJSONPathException $e) {
            // Do nothing
        }

        $json = new JSONFieldOption('$.data', $option);
        static::assertTrue($option->isValid());
        static::assertTrue($json->isValid());
    }

    public function testGetOptionData(): void
    {
        $option = new TextFieldOption();
        $option->setField('data');
        $json = new JSONFieldOption('$.data', $option);

        $expected = [
            'path' => '$.data',
        ] + $option->getOptionData();

        static::assertSame($expected, $json->getOptionData());
    }

    public function testGetVersionConstraint(): void
    {
        $option = new TextFieldOption();
        $json = new JSONFieldOption('$.data', $option);

        static::assertSame('>=2.2.0', $json->getVersionConstraint());
    }

    public function testGetFieldName(): void
    {
        $option = new TextFieldOption();
        $option->setField('data');
        $json = new JSONFieldOption('$.data', $option);

        static::assertSame('data', $json->getFieldName());
    }

    public function testGetJSONPath(): void
    {
        $option = new TextFieldOption();
        $json = new JSONFieldOption('$.data', $option);

        static::assertSame('$.data', $json->getJSONPath());
    }

    public function testDoRender(): void
    {
        $option = new TextFieldOption();
        $option->setField('data');
        $json = new JSONFieldOption('$.data', $option);

        static::assertSame(['$.data', 'AS', 'data', 'TEXT'], $json->render());
    }
}
