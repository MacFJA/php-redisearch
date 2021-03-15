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

use InvalidArgumentException;
use MacFJA\RediSearch\Helper\DataHelper;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * @coversDefaultClass \MacFJA\RediSearch\Helper\DataHelper
 */
class DataHelperTest extends TestCase
{
    /**
     * @covers ::assert
     */
    public function testAssertTrue(): void
    {
        DataHelper::assert(true);
        $this->expectNotToPerformAssertions();
    }

    /**
     * @covers ::assert
     */
    public function testAssertFalse(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The assertion of Tests\MacFJA\RediSearch\Helper\DataHelperTest::testAssertFalse at line ');

        DataHelper::assert(false);
    }

    /**
     * @covers ::assert
     */
    public function testAssertFalseCustomException1(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DataHelper::assert(false, InvalidArgumentException::class);
    }

    /**
     * @covers ::assert
     */
    public function testAssertFalseCustomException2(): void
    {
        $exception = new InvalidArgumentException('not valid');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not valid');

        DataHelper::assert(false, $exception);
    }

    /**
     * @covers ::assert
     */
    public function testAssertFalseWrongCustomException(): void
    {
        $this->expectException(RuntimeException::class);

        DataHelper::assert(false, stdClass::class);
    }

    /**
     * @covers ::assert
     */
    public function testAssertEmbed1(): void
    {
        $runner = function () {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('The assertion of Tests\MacFJA\RediSearch\Helper\DataHelperTest::Tests\MacFJA\RediSearch\Helper\{closure} at line ');
            DataHelper::assert(false);
        };
        $runner();
    }

    /**
     * @covers ::assert
     */
    public function testAssertEmbed2(): void
    {
        $runner = function () {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('The assertion of Tests\MacFJA\RediSearch\Helper\DataHelperTest::testAssertEmbed2 at line ');
            DataHelper::assert(false, null, 2);
        };
        $runner();
    }
}
