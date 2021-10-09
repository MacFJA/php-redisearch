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

namespace MacFJA\RediSearch\tests\Query\Builder;

use MacFJA\RediSearch\Query\Builder\QueryElementAttribute;
use MacFJA\RediSearch\Query\Builder\RawElement;

/**
 * @covers \MacFJA\RediSearch\Query\Builder\QueryElementAttribute
 *
 * @uses \MacFJA\RediSearch\Query\Builder\RawElement
 * @uses \MacFJA\RediSearch\Query\Builder\EncapsulationGroup
 *
 * @internal
 */
class QueryElementAttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testOptions(string $expected, ?float $weight, ?int $slop, ?bool $inOrder, ?bool $phonetic): void
    {
        $baseElement = new RawElement('foo bar');
        $attribute = new QueryElementAttribute($baseElement, $weight, $slop, $inOrder, $phonetic);
        static::assertSame($expected, $attribute->render());
    }

    /**
     * @return array<array<null|bool|float|int|string>>
     */
    public function dataProvider(): array
    {
        return [
            ['foo bar', null, null, null, null],
            ['(foo bar) => { $weight: 0.5; }', 0.5, null, null, null],
            ['(foo bar) => { $weight: 2.0; }', 2.0, null, null, null],
            ['(foo bar) => { $slop: 2; }', null, 2, null, null],
            ['(foo bar) => { $inorder: true; }', null, null, true, null],
            ['(foo bar) => { $inorder: false; }', null, null, false, null],
            ['(foo bar) => { $phonetic: true; }', null, null, null, true],
            ['(foo bar) => { $phonetic: false; }', null, null, null, false],
            ['(foo bar) => { $weight: 1.3; $slop: 4; $inorder: true; $phonetic: false; }', 1.3, 4, true, false],
        ];
    }
}
