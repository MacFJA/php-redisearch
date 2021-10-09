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

use BadMethodCallException;
use function get_class;
use MacFJA\RediSearch\Redis\Command\CreateCommand\CreateCommandFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\GeoFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\NumericFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TagFieldOption;
use MacFJA\RediSearch\Redis\Command\CreateCommand\TextFieldOption;

trait AddFieldOptionTrait
{
    public function addTextField(string $name, bool $noStem = false, ?float $weight = null, ?string $phonetic = null, bool $sortable = false, bool $noIndex = false): self
    {
        return $this->addField(
            (new TextFieldOption())
                ->setField($name)
                ->setNoStem($noStem)
                ->setWeight($weight)
                ->setPhonetic($phonetic)
                ->setSortable($sortable)
                ->setNoIndex($noIndex)
        );
    }

    public function addNumericField(string $name, bool $sortable = false, bool $noIndex = false): self
    {
        return $this->addField(
            (new NumericFieldOption())
                ->setField($name)
                ->setSortable($sortable)
                ->setNoIndex($noIndex)
        );
    }

    public function addGeoField(string $name, bool $noIndex = false): self
    {
        return $this->addField(
            (new GeoFieldOption())
                ->setField($name)
                ->setNoIndex($noIndex)
        );
    }

    public function addTagField(string $name, ?string $separator = null, bool $sortable = false, bool $noIndex = false, bool $caseSensitive = false): self
    {
        return $this->addField(
            (new TagFieldOption())
                ->setField($name)
                ->setSeparator($separator)
                ->setCaseSensitive($caseSensitive)
                ->setSortable($sortable)
                ->setNoIndex($noIndex)
        );
    }

    public function addField(CreateCommandFieldOption $option): self
    {
        if (!($this instanceof AbstractCommand)) {
            throw new BadMethodCallException('This method is not callable in '.get_class($this));
        }

        $this->options['fields'][$option->getFieldName()] = $option;

        return $this;
    }
}
