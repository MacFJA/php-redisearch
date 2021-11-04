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

namespace MacFJA\RediSearch\tests\Redis\Client;

use MacFJA\RediSearch\tests\fixtures\Redis\Client\FakeAbstractClient;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MacFJA\RediSearch\Redis\Client\AbstractClient
 *
 * @internal
 */
class AbstractClientTest extends TestCase
{
    public function testGetMissingMessage(): void
    {
        $fake = new FakeAbstractClient();

        static::assertSame(
            'The dependency foo is missing.'.PHP_EOL.'Install the dependency or use a polyfill.',
            $fake->exposeGetMissingMessage('foo', false, [])
        );

        static::assertSame(
            'The dependency bar is missing.'.PHP_EOL.'Install the dependency or use a polyfill that provide the class "baz" and the method "baz::hello".',
            $fake->exposeGetMissingMessage('bar', false, ['baz' => ['hello']])
        );

        static::assertSame(
            'The dependency baz is missing.'.PHP_EOL.'Install the dependency or use a polyfill that provide the function "foobar".',
            $fake->exposeGetMissingMessage('baz', false, [], ['foobar'])
        );

        static::assertSame(
            'The dependency DEP is missing.'.PHP_EOL.'Install the dependency or use a polyfill that provide the classes "C", "CL" and the methods "C::m", "C::e", "CL::t", "CL::h" and the functions "f", "u".',
            $fake->exposeGetMissingMessage('DEP', false, ['C' => ['m', 'e'], 'CL' => ['t', 'h']], ['f', 'u'])
        );

        static::assertSame(
            'The extension foobar is missing.'.PHP_EOL.'Install the extension or use a polyfill.',
            $fake->exposeGetMissingMessage('foobar', true, [])
        );
    }
}
