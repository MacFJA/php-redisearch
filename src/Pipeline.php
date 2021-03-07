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

namespace MacFJA\RediSearch;

use function array_keys;
use function array_map;
use function assert;
use function is_array;
use MacFJA\RediSearch\Helper\PipelineItem;
use Predis\Client;

class Pipeline
{
    /** @var array<PipelineItem> */
    private $pipeline;

    /** @var Client */
    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
        $this->pipeline = [];
    }

    public function addItem(PipelineItem $pipelineItem): self
    {
        $this->pipeline[] = $pipelineItem;

        return $this;
    }

    public function addPipeable(Pipeable $pipeable): self
    {
        $this->pipeline[] = $pipeable->asPipelineItem();

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function executePipeline(): array
    {
        $rawReplies = $this->redis->pipeline(function (\Predis\Pipeline\Pipeline $pipe) {
            foreach ($this->pipeline as $item) {
                $pipe->executeCommand($item->getCommand());
            }
        });
        assert(is_array($rawReplies));

        try {
            return array_map(function ($reply, $index) {
                return $this->pipeline[$index]->transform($reply);
            }, $rawReplies, array_keys($rawReplies));
        } finally {
            $this->pipeline = [];
        }
    }
}
