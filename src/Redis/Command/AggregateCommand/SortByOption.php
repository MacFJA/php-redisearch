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

namespace MacFJA\RediSearch\Redis\Command\AggregateCommand;

use function count;
use function is_int;

use MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption;
use MacFJA\RediSearch\Redis\Command\PrefixFieldName;

class SortByOption extends AbstractCommandOption
{
    use PrefixFieldName;

    /** @var array<string,null|string> */
    private $fields = [];

    /** @var null|int */
    private $max;

    public function setMax(?int $max): SortByOption
    {
        $this->max = $max;

        return $this;
    }

    public function addField(string $name, ?string $direction = null): void
    {
        $this->fields[self::prefixField($name)] = $direction;
    }

    public function isValid(): bool
    {
        return count($this->fields) > 0;
    }

    /**
     * @codeCoverageIgnore Simple getter
     *
     * @return array<string,null|array<string,null|string>|int>
     */
    public function getOptionData()
    {
        return [
            'fields' => $this->fields,
            'max' => $this->max,
        ];
    }

    protected function doRender(?string $version): array
    {
        $fields = [];
        foreach ($this->fields as $name => $direction) {
            $fields[] = $name;
            if ('ASC' === $direction || 'DESC' === $direction) {
                $fields[] = $direction;
            }
        }
        $max = is_int($this->max) ? ['MAX', $this->max] : [];

        return array_merge(['SORTBY', count($fields)], $fields, $max);
    }
}
