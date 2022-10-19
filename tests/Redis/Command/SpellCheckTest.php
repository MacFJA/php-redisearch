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
use MacFJA\RediSearch\Redis\Command\SpellCheck;
use MacFJA\RediSearch\Redis\Response\SpellCheckResponseItem;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \MacFJA\RediSearch\Exception\UnexpectedServerResponseException
 * @covers  \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @covers \MacFJA\RediSearch\Redis\Command\SpellCheck
 * @covers \MacFJA\RediSearch\Redis\Command\SpellCheckCommand\TermsOption
 **
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\DecoratedOptionTrait
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 *
 * @internal
 */
class SpellCheckTest extends TestCase
{
    public function testGetId(): void
    {
        $command = new SpellCheck();
        static::assertSame('FT.SPELLCHECK', $command->getId());
    }

    public function testFullOption(): void
    {
        $command = new SpellCheck(SpellCheck::MAX_IMPLEMENTED_VERSION);
        $command
            ->setIndex('idx')
            ->setQuery('@text1:"hello world"')
            ->setDistance(2)
            ->addTerms('badword', true)
            ->addTerms('cities', false)
            ->setDialect(2)
        ;

        static::assertSame([
            'idx',
            '@text1:"hello world"',
            'DISTANCE', 2,
            'DIALECT', 2,
            'TERMS', 'EXCLUDE', 'badword',
            'TERMS', 'INCLUDE', 'cities',
        ], $command->getArguments());
    }

    public function testParseResponse(): void
    {
        $command = new SpellCheck();
        $expected = [
            new SpellCheckResponseItem('hell', ['hello' => 0.7, 'hola' => 0.4]),
            new SpellCheckResponseItem('worl', ['world' => 0.9, 'work' => 0.56]),
        ];
        $rawResponse = [
            ['TERM', 'hell', [[0.7, 'hello'], [0.4, 'hola']]],
            ['TERM', 'worl', [[0.9, 'world'], [0.56, 'work']]],
        ];

        $actual = $command->parseResponse($rawResponse);

        static::assertEquals($expected, $actual);
    }

    public function testParseResponseInvalid(): void
    {
        $this->expectException(UnexpectedServerResponseException::class);
        $command = new SpellCheck();

        $command->parseResponse('foobar');
    }

    public function testParseResponsePartialInvalid(): void
    {
        $command = new SpellCheck();
        $expected = [
            new SpellCheckResponseItem('hell', ['hello' => 0.7, 'hola' => 0.4]),
        ];
        $rawResponse = [
            ['TERM', 'hell', [[0.7, 'hello'], [0.4, 'hola']]],
            ['TERMbar', 'worl', [[0.9, 'world'], [0.56, 'work']]],
        ];

        $actual = $command->parseResponse($rawResponse);

        static::assertEquals($expected, $actual);
    }
}
