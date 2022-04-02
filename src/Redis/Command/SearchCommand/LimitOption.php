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

namespace MacFJA\RediSearch\Redis\Command\SearchCommand;

use function is_int;
use MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption;

class LimitOption extends AbstractCommandOption
{
    /** @var null|int */
    private $offset;

    /** @var null|int */
    private $size;

    public function __construct()
    {
        parent::__construct('>=2.0.0');
    }

    public function setOffset(?int $offset): LimitOption
    {
        $this->offset = $offset;

        return $this;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): LimitOption
    {
        $this->size = $size;

        return $this;
    }

    public function isValid(): bool
    {
        return is_int($this->offset) && is_int($this->size) && $this->offset >= 0 && $this->size >= 0;
    }

    /**
     * @return array<string,null|int>
     */
    public function getOptionData()
    {
        return [
            'offset' => $this->offset,
            'size' => $this->size,
        ];
    }

    protected function doRender(?string $version): array
    {
        return ['LIMIT', $this->offset, $this->size];
    }
}
