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

namespace MacFJA\RediSearch\Search;

use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\RedisHelper;

class Summarize implements \MacFJA\RediSearch\PartialQuery
{
    /** @var array<string> */
    private $fields = [];

    /** @var array<string> */
    private $fragments = [];

    /** @var null|int */
    private $length;

    /** @var null|string */
    private $separator;

    /**
     * @param array<string> $fields
     * @param array<string> $fragments
     */
    public function __construct(array $fields = [], array $fragments = [], ?int $length = null, ?string $separator = null)
    {
        DataHelper::assertArrayOf($fields, 'string');
        DataHelper::assertArrayOf($fragments, 'string');
        $this->fields = $fields;
        $this->fragments = $fragments;
        $this->length = $length;
        $this->separator = $separator;
    }

    public function getQueryParts(): array
    {
        $query = ['SUMMARIZE'];
        $query = RedisHelper::buildQueryList($query, [
            'FIELDS' => $this->fields,
            'FRAGS' => $this->fragments,
        ]);

        return RedisHelper::buildQueryNotNull($query, [
            'LEN' => $this->length,
            'SEPARATOR' => $this->separator,
        ]);
    }
}
