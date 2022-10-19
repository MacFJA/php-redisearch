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
use MacFJA\RediSearch\Redis\Command\SugGet;
use MacFJA\RediSearch\Redis\Response\SuggestionResponseItem;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Exception\UnexpectedServerResponseException
 * @covers \MacFJA\RediSearch\Redis\Command\AbstractCommand
 * @covers \MacFJA\RediSearch\Redis\Command\SugGet
 *
 * @uses \MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamelessOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\NamedOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\FlagOption
 * @uses \MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption
 *
 * @internal
 */
class SugGetTest extends TestCase
{
    public function testGetId(): void
    {
        $command = new SugGet();
        static::assertSame('FT.SUGGET', $command->getId());
    }

    public function testFullOption(): void
    {
        $command = new SugGet();
        $command
            ->setDictionary('ac')
            ->setPrefix('hell')
            ->setFuzzy()
            ->setWithScores()
            ->setWithPayloads()
            ->setMax(10)
        ;

        static::assertSame([
            'ac', 'hell', 'FUZZY', 'WITHSCORES', 'WITHPAYLOADS', 'MAX', 10,
        ], $command->getArguments());
    }

    public function testParseResponse(): void
    {
        $expected = [
            new SuggestionResponseItem('hello', 0.4024922251701355, 'greeting'),
            new SuggestionResponseItem('hola', 0.40000000596046448, 'greeting'),
        ];

        $rawResult = [
            'hello', '0.4024922251701355', 'greeting',
            'hola', '0.40000000596046448', 'greeting',
        ];

        $command = new SugGet();
        $command->setWithPayloads()->setWithScores();

        static::assertEquals($expected, $command->parseResponse($rawResult));
    }

    public function testInvalidServerResponse1(): void
    {
        $this->expectException(UnexpectedServerResponseException::class);
        $command = new SugGet();
        $command->parseResponse('foobar');
    }

    public function testInvalidServerResponse2(): void
    {
        $this->expectException(UnexpectedServerResponseException::class);
        $this->expectExceptionMessage('Unexpected response from the Redis server: Incomplete response');
        $command = new SugGet();
        $command->setWithPayloads();
        $command->parseResponse(['foobar']);
    }
}
