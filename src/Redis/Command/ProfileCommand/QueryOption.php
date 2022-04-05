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

namespace MacFJA\RediSearch\Redis\Command\ProfileCommand;

use function assert;
use MacFJA\RediSearch\Redis\Command\Aggregate;
use MacFJA\RediSearch\Redis\Command\Option\AbstractCommandOption;
use MacFJA\RediSearch\Redis\Command\Search;

class QueryOption extends AbstractCommandOption
{
    /**
     * @var null|Aggregate|Search
     */
    private $command;

    /**
     * @param Aggregate|Search $command
     */
    public function setCommand($command): self
    {
        $this->command = $command;

        return $this;
    }

    public function isValid(): bool
    {
        return $this->command instanceof Search || $this->command instanceof Aggregate;
    }

    public function getOptionData()
    {
        return [
            'command' => $this->command,
        ];
    }

    public function isSearch(): bool
    {
        return $this->command instanceof Search;
    }

    public function getIndex(): string
    {
        assert($this->command instanceof Search || $this->command instanceof Aggregate);

        return $this->command->getIndex();
    }

    protected function doRender(?string $version): array
    {
        assert($this->command instanceof Search || $this->command instanceof Aggregate);
        $arguments = $this->command->getArguments();
        // remove index
        array_shift($arguments);

        return array_merge(['QUERY'], $arguments);
    }
}
