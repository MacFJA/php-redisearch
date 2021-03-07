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

namespace MacFJA\RediSearch\Helper;

use function call_user_func;
use Predis\Command\CommandInterface;
use Predis\Command\RawCommand;

class PipelineItem
{
    /** @var CommandInterface */
    private $command;

    /** @var array<mixed> */
    private $context;

    /** @var callable */
    private $transformer;

    /**
     * @param array<mixed> $context
     */
    public function __construct(CommandInterface $command, callable $transformer, array $context = [])
    {
        $this->command = $command;
        $this->context = $context;
        $this->transformer = $transformer;
    }

    /**
     * @param array<mixed> $params
     * @param array<mixed> $context
     */
    public static function createFromRaw(array $params, callable $transformer, array $context = []): PipelineItem
    {
        return new self(new RawCommand($params), $transformer, $context);
    }

    public function getCommand(): CommandInterface
    {
        return $this->command;
    }

    /**
     * @return array<mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function getTransformer(): callable
    {
        return $this->transformer;
    }

    /**
     * @param mixed $rawResult
     *
     * @return mixed
     */
    public function transform($rawResult)
    {
        return call_user_func($this->transformer, $rawResult, $this->context);
    }
}
