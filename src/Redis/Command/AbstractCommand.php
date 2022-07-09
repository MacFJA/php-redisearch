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

use function count;
use function is_array;

use InvalidArgumentException;
use MacFJA\RediSearch\Redis\Command;
use MacFJA\RediSearch\Redis\Command\Option\CommandOption;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractCommand implements Command
{
    public const MAX_IMPLEMENTED_VERSION = '2.4.0';
    public const MIN_IMPLEMENTED_VERSION = '2.0.0';

    /**
     * @var array<array<CommandOption>|CommandOption|mixed>
     */
    protected $options;

    /** @var string */
    private $rediSearchVersion;

    /**
     * @param array<array<CommandOption>|CommandOption|mixed> $options
     */
    public function __construct(array $options, string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION)
    {
        $this->rediSearchVersion = $rediSearchVersion;

        $this->options = $options;
    }

    public function getRediSearchVersion(): string
    {
        return $this->rediSearchVersion;
    }

    public function setRediSearchVersion(string $rediSearchVersion): Command
    {
        $this->rediSearchVersion = $rediSearchVersion;

        return $this;
    }

    /**
     * @return array<float|int|string>
     */
    public function getArguments(): array
    {
        if (count($missing = $this->validateRequirements()) > 0) {
            throw new InvalidArgumentException('Missing command option: '.implode(', ', $missing));
        }

        $options = array_reduce($this->options, static function ($flatten, $item) {
            if (!is_array($item)) {
                $item = [$item];
            }

            return array_merge($flatten, $item);
        }, []);
        $arguments = array_filter($options, function (CommandOption $option) {
            return $option->isCompatible($this->rediSearchVersion) && $option->isValid();
        });
        $arguments = $this->sortArguments($arguments);

        return array_reduce($arguments, function ($carry, CommandOption $option) {
            return array_merge($carry, $option->render($this->rediSearchVersion));
        }, []);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param array<mixed>|float|int|mixed|string $data
     *
     * @return mixed
     */
    public function parseResponse($data)
    {
        return $data;
    }

    /**
     * @param array<CommandOption> $arguments
     *
     * @return array<CommandOption>
     */
    protected function sortArguments(array $arguments): array
    {
        return $arguments;
    }

    /**
     * @return array<string>
     */
    abstract protected function getRequiredOptions(): array;

    /**
     * @return array<string>
     */
    private function validateRequirements(): array
    {
        $arguments = array_filter($this->options, function ($option) {
            if ($option instanceof CommandOption) {
                return $option->isCompatible($this->rediSearchVersion) && $option->isValid();
            }
            if (is_array($option)) {
                $option = array_filter($option, function (CommandOption $option) {
                    return $option->isCompatible($this->rediSearchVersion) && $option->isValid();
                });

                return count($option) > 0;
            }

            return false;
        });

        return array_diff($this->getRequiredOptions(), array_keys($arguments));
    }
}
