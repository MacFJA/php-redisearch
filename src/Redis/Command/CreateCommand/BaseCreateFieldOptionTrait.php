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

use MacFJA\RediSearch\Redis\Command\Option\CommandOption;
use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use Respect\Validation\Rules\Callback;

trait BaseCreateFieldOptionTrait
{
    /**
     * @param array<array<CommandOption>|CommandOption|mixed> $options $options
     *
     * @return array<array<CommandOption>|CommandOption|mixed> $options
     */
    private function getConstructorOptions(string $type, array $options = []): array
    {
        $self = $this;

        return array_merge(
            [
                'field' => new NamelessOption(null, '>=2.0.0'),
                'type' => new FlagOption($type, true, '>=2.0.0'),
            ],
            $options,
            [
                'sortable' => new FlagOption('SORTABLE', false, '>=2.0.0'),
                'un_normalized_sortable' => new CustomValidatorOption(new FlagOption('UNF', false, '>=2.0.12'), new Callback(static function () use ($self) {
                    return $self->getDataOfOption('sortable');
                })),
                'no_index' => new FlagOption('NOINDEX', false, '>=2.0.0'),
            ]
        );
    }
}
