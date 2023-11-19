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

use BadMethodCallException;

/**
 * @template T of CommandOption
 *
 * @mixin T
 */
trait DecoratedOptionTrait
{
    /**
     * @var CommandOption
     *
     * @phpstan-var T
     */
    private $decorated;

    /**
     * @param array<mixed> $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->decorated, $name)) {
            return $this->decorated->{$name}(...$arguments);
        }

        throw new BadMethodCallException('Call undefined method '.$name);
    }

    public function isCompatible(?string $version): bool
    {
        return $this->decorated->isCompatible($version);
    }

    public function getVersionConstraint(): string
    {
        return $this->decorated->getVersionConstraint();
    }

    /**
     * @return array<float|int|string>
     */
    public function render(?string $version = null): array
    {
        return $this->decorated->render($version);
    }

    public function isValid(): bool
    {
        return $this->decorated->isValid();
    }

    /**
     * @return mixed
     */
    public function getOptionData()
    {
        return $this->decorated->getOptionData();
    }

    /**
     * @phpstan-return T
     */
    public function getDecoratedOption(): CommandOption
    {
        return $this->decorated;
    }

    /**
     * @phpstan-param T $decorated
     */
    private function setDecoratedOption(CommandOption $decorated): void
    {
        $this->decorated = $decorated;
    }
}
