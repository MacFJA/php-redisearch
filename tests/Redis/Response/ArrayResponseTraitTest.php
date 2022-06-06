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

namespace MacFJA\RediSearch\tests\Redis\Response;

use MacFJA\RediSearch\tests\fixtures\Redis\Response\ArrayResponseTraitTester;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Response\ArrayResponseTrait
 *
 * @internal
 */
class ArrayResponseTraitTest extends TestCase
{
    public function testOddGetPairs(): void
    {
        $data = [1, 2, 3];

        $inlineArrayResponse = $this->getInlineArrayResponse();

        static::assertSame(['1' => 2], $inlineArrayResponse->publicGetPairs($data));
    }

    public function testEvenGetPairs(): void
    {
        $data = [1, 2, 3, 4];

        $inlineArrayResponse = $this->getInlineArrayResponse();

        static::assertSame(['1' => 2, '3' => 4], $inlineArrayResponse->publicGetPairs($data));
    }

    public function testOddGetKeys(): void
    {
        $data = [1, 2, 3];

        $inlineArrayResponse = $this->getInlineArrayResponse();

        static::assertSame(['1'], $inlineArrayResponse->publicGetKeys($data));
    }

    public function testEvenGetKeys(): void
    {
        $data = [1, 2, 3, 4];

        $inlineArrayResponse = $this->getInlineArrayResponse();

        static::assertSame(['1', '3'], $inlineArrayResponse->publicGetKeys($data));
    }

    public function testOddGetValue(): void
    {
        $data = [1, 2, 3];

        $inlineArrayResponse = $this->getInlineArrayResponse();

        static::assertSame(2, $inlineArrayResponse->publicGetValue($data, '1'));
        static::assertNull($inlineArrayResponse->publicGetValue($data, '3'));
    }

    public function testEvenGetValue(): void
    {
        $data = [1, 2, 3, 4];

        $inlineArrayResponse = $this->getInlineArrayResponse();

        static::assertSame(2, $inlineArrayResponse->publicGetValue($data, '1'));
        static::assertSame(4, $inlineArrayResponse->publicGetValue($data, '3'));
        static::assertNull($inlineArrayResponse->publicGetValue($data, '6'));
    }

    public function testOddGetNextValue(): void
    {
        $data = [1, 2, 3];

        $inlineArrayResponse = $this->getInlineArrayResponse();

        static::assertSame(2, $inlineArrayResponse->publicGetNextValue($data, '1'));
        static::assertSame(3, $inlineArrayResponse->publicGetNextValue($data, '2'));
        static::assertNull($inlineArrayResponse->publicGetNextValue($data, '3'));
    }

    public function testEvenGetNextValue(): void
    {
        $data = [1, 2, 3, 4];

        $inlineArrayResponse = $this->getInlineArrayResponse();

        static::assertSame(2, $inlineArrayResponse->publicGetNextValue($data, '1'));
        static::assertSame(3, $inlineArrayResponse->publicGetNextValue($data, '2'));
        static::assertSame(4, $inlineArrayResponse->publicGetNextValue($data, '3'));
        static::assertNull($inlineArrayResponse->publicGetNextValue($data, '6'));
    }

    private function getInlineArrayResponse(): ArrayResponseTraitTester
    {
        return new ArrayResponseTraitTester();
    }
}
