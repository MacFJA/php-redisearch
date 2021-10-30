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

namespace MacFJA\RediSearch\Redis\Command;

use MacFJA\RediSearch\Redis\Command;

class IndexList implements Command
{
    /** @var string */
    private $rediSearchVersion = AbstractCommand::MIN_IMPLEMENTED_VERSION;

    public function getId(): string
    {
        return 'FT._LIST';
    }

    public function getRediSearchVersion(): string
    {
        return $this->rediSearchVersion;
    }

    /**
     * @return IndexList
     */
    public function setRediSearchVersion(string $rediSearchVersion): Command
    {
        $this->rediSearchVersion = $rediSearchVersion;

        return $this;
    }

    public function getArguments(): array
    {
        return [];
    }

    public function parseResponse($data)
    {
        return $data;
    }
}
