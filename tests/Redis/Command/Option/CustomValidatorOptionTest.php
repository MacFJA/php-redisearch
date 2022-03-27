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

use Generator;
use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use Respect\Validation\Rules\Length;

/**
 * @covers \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @covers \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @covers \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 *
 * @internal
 */
class CustomValidatorOptionTest extends \PHPUnit\Framework\TestCase
{
    use BaseOptionTestTrait;
    use TestIsCompatibleTrait;

    public function testGetValidator(): void
    {
        $validator = new Length(4);
        $option = new CustomValidatorOption(new NamelessOption(), $validator);

        static::assertSame($validator, $option->getValidator());
    }

    public function testShorthands(): void
    {
        $validNumeric = CustomValidatorOption::isNumeric(new NamelessOption('10'));
        $invalidNumeric = CustomValidatorOption::isNumeric(new NamelessOption('foo'));
        $validList = CustomValidatorOption::allowedValues(new NamelessOption('foo'), ['foo', 'bar']);
        $invalidList = CustomValidatorOption::allowedValues(new NamelessOption('foobar'), ['foo', 'bar']);

        static::assertTrue($validNumeric->isValid());
        static::assertTrue($validList->isValid());
        static::assertFalse($invalidNumeric->isValid());
        static::assertFalse($invalidList->isValid());
    }

    public function dataProvider(string $testName): Generator
    {
        $invalidMinLengthOption = new CustomValidatorOption(new NamelessOption('foo'), new Length(4));
        $emptyMinLengthOption = new CustomValidatorOption(new NamelessOption(), new Length(4));
        $validMinLengthOption = new CustomValidatorOption(new NamelessOption('foobar'), new Length(4));
        $lowValidMinLengthOption = new CustomValidatorOption(new NamelessOption('foobar', '>=1.0.0'), new Length(4));
        $highValidMinLengthOption = new CustomValidatorOption(new NamelessOption('foobar', '>=2.0.0'), new Length(4));

        switch ($testName) {
            case 'testGetOptionData':
                yield [$invalidMinLengthOption, 'foo'];

                yield [$validMinLengthOption, 'foobar'];

                yield [$emptyMinLengthOption, null];

                break;

            case 'testIsValid':
                yield [$invalidMinLengthOption, false];

                yield [$validMinLengthOption, true];

                yield [$emptyMinLengthOption, false];

                break;

            case 'testRender':
                yield [$invalidMinLengthOption, []];

                yield [$validMinLengthOption, ['foobar']];

                yield [$emptyMinLengthOption, []];

                break;

            case 'testIsCompatible':
                yield [$validMinLengthOption, '1.0.0', true];

                yield [$validMinLengthOption, '1.5.0', true];

                yield [$validMinLengthOption, '2.0.0', true];

                yield [$validMinLengthOption, '2.5.0', true];

                yield [$validMinLengthOption, 'foo', false];

                yield [$lowValidMinLengthOption, '1.0.0', true];

                yield [$lowValidMinLengthOption, '1.5.0', true];

                yield [$lowValidMinLengthOption, '2.0.0', true];

                yield [$lowValidMinLengthOption, '2.5.0', true];

                yield [$lowValidMinLengthOption, 'foo', false];

                yield [$highValidMinLengthOption, '1.0.0', false];

                yield [$highValidMinLengthOption, '1.5.0', false];

                yield [$highValidMinLengthOption, '2.0.0', true];

                yield [$highValidMinLengthOption, '2.5.0', true];

                yield [$highValidMinLengthOption, 'foo', false];

                break;

            default:
                return;
        }
    }
}
