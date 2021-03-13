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

namespace MacFJA\RediSearch\Index\Builder;

use MacFJA\RediSearch\Helper\DataHelper;
use MacFJA\RediSearch\Helper\RedisHelper;
use MacFJA\RediSearch\Index\Builder\Exception\MustBeSingleCharException;
use function strlen;

class TagField extends AbstractField
{
    /** @var null|string */
    private $separator;

    public function __construct(string $name, ?string $separator = null, bool $sortable = false, bool $noIndex = false)
    {
        DataHelper::assert(
            null === $separator || 1 === strlen($separator),
            new MustBeSingleCharException('Separator must be a single char')
        );
        parent::__construct($name, $sortable, $noIndex);
        $this->separator = $separator;
    }

    public function getType(): string
    {
        return self::TYPE_TAG;
    }

    public function getSeparator(): ?string
    {
        return $this->separator;
    }

    protected function getAdditionalQueryParts(): array
    {
        return RedisHelper::buildQueryNotNull([], [
            'SEPARATOR' => $this->separator,
        ]);
    }
}
