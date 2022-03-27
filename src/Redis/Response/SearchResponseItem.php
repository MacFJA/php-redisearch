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

namespace MacFJA\RediSearch\Redis\Response;

/**
 * @codeCoverageIgnore Simple value object
 */
class SearchResponseItem implements ResponseItem
{
    /** @var string */
    private $hash;

    /** @var null|float */
    private $score;

    /** @var null|string */
    private $payload;

    /** @var array<string,bool|float|int|string> */
    private $fields = [];

    /** @var null|string */
    private $sortKey;

    /**
     * @param array<string,bool|float|int|string> $fields
     */
    public function __construct(string $hash, array $fields = [], ?float $score = null, ?string $payload = null, ?string $sortKey = null)
    {
        $this->hash = $hash;
        $this->score = $score;
        $this->payload = $payload;
        $this->fields = $fields;
        $this->sortKey = $sortKey;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    /**
     * @return array<string,bool|float|int|string>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param null|array<mixed>|bool|float|int|string $default
     *
     * @return null|array<mixed>|bool|float|int|string
     */
    public function getFieldValue(string $fieldName, $default = null)
    {
        return $this->fields[$fieldName] ?? $default;
    }

    public function getSortKey(): ?string
    {
        return $this->sortKey;
    }
}
