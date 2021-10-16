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

namespace MacFJA\RediSearch\tests\Redis;

use Generator;
use MacFJA\RediSearch\Redis\Client;
use MacFJA\RediSearch\Redis\Initializer;

/**
 * @covers \MacFJA\RediSearch\Redis\Initializer
 *
 * @internal
 */
class InitializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array<string> $input
     * @dataProvider dataProvider
     */
    public function testRediSearchVersion(array $input, ?string $expected): void
    {
        $client = $this->createMock(Client::class);
        $client->method('executeRaw')->with('info', 'Modules')->willReturn(['Modules' => $input]);

        static::assertSame($expected, Initializer::getRediSearchVersion($client));
    }

    /**
     * @return Generator<array>
     */
    public function dataProvider(string $testName): Generator
    {
        if ('testRediSearchVersion' === $testName) {
            yield [['name=search,ver=20005,api=1,filters=0,usedby=[],using=[],options=[]'], '2.0.5'];
            yield [['name=search,ver=20000,api=1,filters=0,usedby=[],using=[],options=[]'], '2.0.0'];
            yield [['name=search,ver=26512,api=1,filters=0,usedby=[],using=[],options=[]'], '2.65.12'];
            yield [['name=search,ver=120569,api=1,filters=0,usedby=[],using=[],options=[]'], '12.5.69'];
            yield [['name=search,ver=99999,api=1,filters=0,usedby=[],using=[],options=[]'], '9.99.99'];
            yield [['name=json,ver=99999,api=1,filters=0,usedby=[],using=[],options=[]'], null];
            yield [['name=json,ver=99999,api=1,filters=0,usedby=[],using=[],options=[]', 'name=search,ver=20005,api=1,filters=0,usedby=[],using=[],options=[]'], '2.0.5'];
        }
    }
}
