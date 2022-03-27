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

namespace MacFJA\RediSearch\Redis\Command\CreateCommand;

use MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption;

class JSONFieldOption extends AbstractCommandOption implements CreateCommandJSONFieldOption
{
    /** @var CreateCommandFieldOption */
    private $decorated;

    /** @var string */
    private $path;

    public function __construct(string $path, CreateCommandFieldOption $decorated)
    {
        parent::__construct('>=2.2.0');
        $this->path = $path;
        $this->decorated = $decorated;
    }

    public function isValid(): bool
    {
        return !empty($this->path) && $this->decorated->isValid();
    }

    public function getOptionData()
    {
        return array_merge(['path' => $this->path], $this->decorated->getOptionData());
    }

    public function getVersionConstraint(): string
    {
        return '>=2.2.0';
    }

    public function getFieldName(): string
    {
        return $this->decorated->getFieldName();
    }

    public function getJSONPath(): string
    {
        return $this->path;
    }

    protected function doRender(?string $version): array
    {
        return array_merge([$this->path, 'AS'], $this->decorated->render($version));
    }
}
