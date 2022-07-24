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
use function get_class;
use function in_array;

use BadMethodCallException;
use InvalidArgumentException;

trait WithPublicGroupedSetterTrait
{
    /**
     * @param array<mixed> $arguments
     *
     * @return $this
     */
    public function __call(string $name, array $arguments): self
    {
        if (!$this instanceof GroupedOption) {
            throw new BadMethodCallException('This method is not callable in '.get_class($this));
        }
        if (0 === strpos($name, 'set') && $name[3] === strtoupper($name[3])) {
            $pascalCase = substr($name, 3);
            $snakeCase = strtolower(
                trim(
                    preg_replace('/([A-Z])/', '_$1', $pascalCase) ?? $pascalCase,
                    '_'
                )
            );

            if (!in_array($snakeCase, $this->publicSetter(), true)) {
                throw new BadMethodCallException(sprintf('Call undefined method %s in %s', $name, get_class($this)));
            }
            if (!(1 === count($arguments))) {
                throw new InvalidArgumentException(sprintf('The method %s::%s need exactly one argument', get_class($this), $name));
            }

            return $this->setDataOfOption($snakeCase, $arguments[0]);
        }

        throw new BadMethodCallException(sprintf('Call undefined method %s in %s', $name, get_class($this)));
    }

    /**
     * @return array<string>
     */
    abstract protected function publicSetter(): array;
}
