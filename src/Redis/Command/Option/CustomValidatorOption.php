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

use Respect\Validation\Rules\In;
use Respect\Validation\Rules\NumericVal;
use Respect\Validation\Validatable;

/**
 * @template T of CommandOption
 *
 * @mixin T
 */
class CustomValidatorOption implements CommandOption, DecoratedCommandOption
{
    use DecoratedOptionTrait { render as decoratedRender; }

    /** @var Validatable */
    private $validator;

    /**
     * @phpstan-param T $decorated
     */
    public function __construct(CommandOption $decorated, Validatable $validator)
    {
        $this->validator = $validator;
        $this->setDecoratedOption($decorated);
    }

    /**
     * @phpstan-param T $decorated
     *
     * @psalm-param CommandOption $decorated
     *
     * @param array<float|int|string> $allowed
     *
     * @return CustomValidatorOption<T>
     */
    public static function allowedValues(CommandOption $decorated, array $allowed): self
    {
        return new self($decorated, new In($allowed));
    }

    /**
     * @phpstan-param T $decorated
     *
     * @psalm-param CommandOption $decorated
     *
     * @return static<T>
     */
    public static function isNumeric(CommandOption $decorated): self
    {
        return new self($decorated, new NumericVal());
    }

    public function isValid(): bool
    {
        return $this->validator->validate($this->getDecoratedOption()->getOptionData());
    }

    public function getValidator(): Validatable
    {
        return $this->validator;
    }

    public function render(?string $version = null): array
    {
        if ($this->isValid()) {
            return $this->decoratedRender($version);
        }

        return [];
    }
}
