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

namespace Tests\MacFJA\RediSearch\Helper;

use MacFJA\RediSearch\Helper\RedisHelper;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Helper\RedisHelper
 */
class RedisHelperTest extends TestCase
{
    /**
     * @covers ::buildQueryBoolean
     */
    public function testBuildQueryBoolean(): void
    {
        self::assertSame([], RedisHelper::buildQueryBoolean([], []));
        self::assertSame([], RedisHelper::buildQueryBoolean([], ['foobar' => false]));
        self::assertSame(['foobar'], RedisHelper::buildQueryBoolean([], ['foobar' => true]));
        self::assertSame(['foobar', 'foo'], RedisHelper::buildQueryBoolean([], ['foobar' => true, 'foo' => true, 'bar' => false]));
        self::assertSame(['foo', 'bar'], RedisHelper::buildQueryBoolean(['foo'], ['bar' => true]));
    }

    /**
     * @covers ::buildQueryList
     */
    public function testBuildQueryList(): void
    {
        self::assertSame([], RedisHelper::buildQueryList([], []));
        self::assertSame([], RedisHelper::buildQueryList([], ['foobar' => []]));
        self::assertSame(['foobar', 2, 'foo', 'bar'], RedisHelper::buildQueryList([], ['foobar' => ['foo', 'bar']]));
        self::assertSame(['foobar', 0], RedisHelper::buildQueryList([], ['foobar' => []], true));
        self::assertSame(['foo', 2, 'foo', 'bar', 'bar', 1, 'foobar'], RedisHelper::buildQueryList([], ['foo' => ['foo', 'bar'], 'bar' => ['foobar']]));
        self::assertSame(['foobar', 2, 2, 'bar'], RedisHelper::buildQueryList([], ['foobar' => [2, 'bar']]));
        self::assertSame(['foo', 'foobar', 2, 2, 'bar'], RedisHelper::buildQueryList(['foo'], ['foobar' => [2, 'bar']]));
    }

    /**
     * @covers ::buildQueryNotNull
     */
    public function testBuildQueryNotNull(): void
    {
        self::assertSame([], RedisHelper::buildQueryNotNull([], []));
        self::assertSame([], RedisHelper::buildQueryNotNull([], ['foobar' => null]));
        self::assertSame(['foobar', 2], RedisHelper::buildQueryNotNull([], ['foobar' => 2]));
        self::assertSame(['foo', 'bar'], RedisHelper::buildQueryNotNull([], ['foo' => 'bar']));
        self::assertSame(['foo', 'bar', 'hello', 'world'], RedisHelper::buildQueryNotNull([], ['foo' => 'bar', 'hello' => 'world']));
        self::assertSame(['foobar', 'foo', 'bar'], RedisHelper::buildQueryNotNull(['foobar'], ['foo' => 'bar']));
    }

    /**
     * @covers ::getPairs
     */
    public function testGetPairs(): void
    {
        self::assertSame(['key1' => 'value1', 'key2' => 'value2'], RedisHelper::getPairs(['key1', 'value1', 'key2', 'value2']));
        self::assertSame(['key1' => 'value1'], RedisHelper::getPairs(['key1', 'value1', 'key2']));
    }

    /**
     * @covers ::getValue
     */
    public function testGetValue(): void
    {
        self::assertSame('value1', RedisHelper::getValue(['key1', 'value1', 'key2', 'value2'], 'key1'));
        self::assertSame('value2', RedisHelper::getValue(['key1', 'value1', 'key2', 'value2'], 'key2'));
        self::assertNull(RedisHelper::getValue(['key1', 'value1', 'key2', 'value2'], 'key3'));
        self::assertSame('value1', RedisHelper::getValue(['key1', 'value1', 'key2'], 'key1'));
        self::assertNull(RedisHelper::getValue(['key1', 'value1', 'key2'], 'key2'));
        self::assertSame('value', RedisHelper::getValue(['foo', 'bar', 'foobar', 'key', 'value'], 'key'));
    }
}
