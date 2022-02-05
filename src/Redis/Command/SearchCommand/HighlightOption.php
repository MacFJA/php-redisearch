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

use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\GroupedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption;
use MacFJA\RediSearch\Redis\Command\Option\NumberedOption;

class HighlightOption extends GroupedOption
{
    public function __construct()
    {
        parent::__construct([
            'type' => new FlagOption('HIGHLIGHT', false, '>=2.0.0'),
            'fields' => new NotEmptyOption(new NumberedOption('FIELDS', null, '>=2.0.0')),
            'tag' => new GroupedOption([
                'type' => new FlagOption('TAGS', true, '>=2.0.0'),
                'open' => new NamelessOption(null, '>=2.0.0'),
                'close' => new NamelessOption(null, '>=2.0.0'),
            ], ['type', 'open', 'close'], ['type'], '>=2.0.0'),
        ], ['type'], [], '>=2.0.0');
    }

    public function setTags(?string $open, ?string $close): self
    {
        /** @var GroupedOption $tag */
        $tag = $this->getOption('tag');
        $tag->setDataOfOption('open', $open)
            ->setDataOfOption('close', $close)
        ;

        return $this;
    }
}
