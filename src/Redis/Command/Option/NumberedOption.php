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

namespace MacFJA\RediSearch\Redis\Command\Option;

use function assert;
use function count;
use function is_array;

class NumberedOption extends AbstractCommandOption
{
    /** @var string */
    private $name;
    /** @var null|array<float|int|string> */
    private $arguments;

    /**
     * @param null|array<float|int|string> $arguments
     */
    public function __construct(string $name, ?array $arguments = null, ?string $versionConstraint = null)
    {
        $this->arguments = $arguments;
        $this->name = $name;
        parent::__construct($versionConstraint);
    }

    /**
     * @param array<float|int|string> $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function isValid(): bool
    {
        return is_array($this->arguments);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|array<float|int|string>
     */
    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    /**
     * @return null|array<mixed>
     */
    public function getOptionData()
    {
        return $this->getArguments();
    }

    protected function doRender(?string $version): array
    {
        assert(is_array($this->arguments));

        return array_merge([$this->name, count($this->arguments)], $this->arguments);
    }
}
