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

namespace Tests\MacFJA\RediSearch\Index\Builder;

use MacFJA\RediSearch\Index\Builder\TextField;
use PHPUnit\Framework\TestCase;
use Tests\MacFJA\RediSearch\support\Assertion;

/**
 * @covers \MacFJA\RediSearch\Index\Builder\TextField
 *
 * @uses \MacFJA\RediSearch\Index\Builder\AbstractField
 * @uses \MacFJA\RediSearch\Helper\RedisHelper
 */
class TextFieldTest extends TestCase
{
    use Assertion;

    public function testNominal(): void
    {
        self::assertSameQuery(
            'title TEXT WEIGHT 2 PHONETIC dm:en SORTABLE',
            new TextField('title', false, 2, 'dm:en', true, false)
        );
        self::assertSameQuery(
            'name TEXT NOSTEM NOINDEX',
            new TextField('name', true, null, null, false, true)
        );
        self::assertSameQuery(
            'code TEXT NOSTEM WEIGHT 4.5',
            new TextField('code', true, 4.5)
        );
    }

    public function testGetType(): void
    {
        self::assertSame('TEXT', (new TextField('foobar'))->getType());
    }

    public function testIsNoStem(): void
    {
        $text = new TextField('foobar', false);
        self::assertFalse($text->isNoStem());

        $text = new TextField('foobar');
        self::assertFalse($text->isNoStem());

        $text = new TextField('foobar', true);
        self::assertTrue($text->isNoStem());
    }

    public function testGetWeight(): void
    {
        $text = new TextField('foobar', false, 1);
        self::assertSame(1.0, $text->getWeight());

        $text = new TextField('foobar', false, null);
        self::assertNull($text->getWeight());

        $text = new TextField('foobar');
        self::assertNull($text->getWeight());
    }

    public function testGetPhonetic(): void
    {
        $text = new TextField('foobar', false, null, 'dm:en');
        self::assertSame('dm:en', $text->getPhonetic());

        $text = new TextField('foobar', false, null, null);
        self::assertNull($text->getPhonetic());

        $text = new TextField('foobar');
        self::assertNull($text->getPhonetic());
    }
}
