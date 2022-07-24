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

use function count;

/**
 * @template T of CommandOption
 * @mixin T
 */
class NotEmptyOption implements CommandOption, DecoratedCommandOption
{
    use DecoratedOptionTrait {
        isValid as decoratedIsValid;
        render as decoratedRender;
    }

    /**
     * @phpstan-param T $decorated
     */
    public function __construct(CommandOption $decorated)
    {
        $this->setDecoratedOption($decorated);
    }

    public function render(?string $version = null): array
    {
        if (!$this->isValid()) {
            return [];
        }

        return $this->decoratedRender($version);
    }

    public function isValid(): bool
    {
        if (!$this->decoratedIsValid()) {
            return false;
        }

        $option = $this->getDecoratedOption();

        if ($option instanceof NamelessOption && empty($option->getValue())) {
            return false;
        }

        if ($option instanceof NumberedOption && 0 === count($option->getArguments() ?? [])) {
            return false;
        }

        if ($option instanceof FlagOption && !$option->isActive()) {
            return false;
        }

        return true;
    }
}
