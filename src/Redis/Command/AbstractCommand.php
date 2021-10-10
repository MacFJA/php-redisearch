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
use InvalidArgumentException;
use function is_array;
use MacFJA\RediSearch\Redis\Command\Option\CommandOption;
use Predis\Command\Command;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractCommand extends Command
{
    public const MAX_IMPLEMENTED_VERSION = '2.0.12';
    public const MIN_IMPLEMENTED_VERSION = '2.0.0';
    /**
     * @var array<array<CommandOption>|CommandOption|mixed>
     */
    protected $options;
    /** @var string */
    private $rediSearchVersion;
    /** @var bool */
    private $rebuild = true;

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

    public function setRediSearchVersion(string $rediSearchVersion): self
    {
        $this->rediSearchVersion = $rediSearchVersion;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $index
     */
    public function getArgument($index)
    {
        if ($this->rebuild) {
            $this->buildArguments();
        }

        return parent::getArgument($index);
    }

    /**
     * @return array<mixed>
     */
    public function getArguments()
    {
        if ($this->rebuild) {
            $this->buildArguments();
        }

        return parent::getArguments();
    }

    /**
     * @param array<mixed> $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->rebuild = false;
        parent::setArguments($arguments);
    }

    /**
     * @param array<mixed> $arguments
     */
    public function setRawArguments(array $arguments): void
    {
        $this->rebuild = true;
        parent::setRawArguments($arguments);
    }

    /**
     * @param array<mixed>|float|int|mixed|string $data
     *
     * @return mixed|string
     */
    public function parseResponse($data)
    {
        if ($this->rebuild) {
            return $this->transformParsedResponse(parent::parseResponse($data));
        }

        return parent::parseResponse($data);
    }

    /**
     * @return array<string>
     */
    abstract protected function getRequiredOptions(): array;

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function transformParsedResponse($data)
    {
        return $data;
    }

    private function buildArguments(): void
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

        $arguments = array_reduce($arguments, function ($carry, CommandOption $option) {
            return array_merge($carry, $option->render($this->rediSearchVersion));
        }, []);
        $this->setRawArguments($arguments);
    }

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
