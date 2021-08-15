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

use BadMethodCallException;
use function count;
use function in_array;
use InvalidArgumentException;
use function is_array;
use function is_float;
use function is_int;
use function is_string;
use MacFJA\RediSearch\Redis\Command\AddFieldOptionTrait;
use MacFJA\RediSearch\Redis\Command\Create;
use MacFJA\RediSearch\Redis\Command\CreateCommand\CreateCommandFieldOption;
use Predis\ClientInterface;
use Predis\Response\ResponseInterface;
use RuntimeException;
use function strlen;

/**
 * @method IndexBuilder setIndex(string $name)
 * @method IndexBuilder withIndex(string $name)
 * @method IndexBuilder setStructure(string $type)
 * @method IndexBuilder withStructure(string $type)
 * @method IndexBuilder setPrefixes(array $prefixes)
 * @method IndexBuilder withPrefixes(array $prefixes)
 * @method IndexBuilder addPrefix(string ...$prefixes)
 * @method IndexBuilder withAddedPrefix(string ...$prefixes)
 * @method IndexBuilder setFilter(string $filter)
 * @method IndexBuilder withFilter(string $filter)
 * @method IndexBuilder setDefaultLanguage(string $language)
 * @method IndexBuilder withDefaultLanguage(string $language)
 * @method IndexBuilder setLanguageField(string $field)
 * @method IndexBuilder withLanguageField(string $field)
 * @method IndexBuilder setDefaultScore(float $score)
 * @method IndexBuilder withDefaultScore(float $score)
 * @method IndexBuilder setScoreField(string $field)
 * @method IndexBuilder withScoreField(string $field)
 * @method IndexBuilder setPayloadField(string $field)
 * @method IndexBuilder withPayloadField(string $field)
 * @method IndexBuilder setMaxTextFields(bool $active)
 * @method IndexBuilder withMaxTextFields(bool $active)
 * @method IndexBuilder setTemporary(int $seconds)
 * @method IndexBuilder withTemporary(int $seconds)
 * @method IndexBuilder setNoOffsets(bool $active)
 * @method IndexBuilder withNoOffsets(bool $active)
 * @method IndexBuilder setNoHighLight(bool $active)
 * @method IndexBuilder withNoHighLight(bool $active)
 * @method IndexBuilder setNoFields(bool $active)
 * @method IndexBuilder withNoFields(bool $active)
 * @method IndexBuilder setNoFrequencies(bool $active)
 * @method IndexBuilder withNoFrequencies(bool $active)
 * @method IndexBuilder setSkipInitialScan(bool $active)
 * @method IndexBuilder withSkipInitialScan(bool $active)
 * @method IndexBuilder setStopWords(array $words)
 * @method IndexBuilder withStopWords(array $words)
 * @method IndexBuilder addStopWord(string ...$words)
 * @method IndexBuilder withAddedStopWord(string ...$words)
 * @method IndexBuilder setFields(array $fields)
 * @method IndexBuilder withFields(array $fields)
 * @method IndexBuilder withAddedField(CreateCommandFieldOption ...$fields)
 * @method IndexBuilder withAddedTextField(string $name, bool $noStem = false, ?float $weight = null, ?string $phonetic = null, bool $sortable = false, bool $noIndex = false)
 * @method IndexBuilder withAddedNumericField(string $name, bool $sortable = false, bool $noIndex = false)
 * @method IndexBuilder withAddedGeoField(string $name, bool $noIndex = false)
 * @method IndexBuilder withAddedTagField(string $name, ?string $separator = null, bool $sortable = false, bool $noIndex = false)
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class IndexBuilder
{
    use AddFieldOptionTrait;

    private const IS_ARRAY = ['prefixes', 'stopWords', 'fields'];
    /** @var null|string */
    private $index;
    /** @var null|string */
    private $structure;
    /** @var array<string> */
    private $prefixes = [];
    /** @var null|string */
    private $filter;
    /** @var null|string */
    private $defaultLanguage;
    /** @var null|string */
    private $languageField;
    /** @var null|float */
    private $defaultScore;
    /** @var null|string */
    private $scoreField;
    /** @var null|string */
    private $payloadField;
    /** @var bool */
    private $maxTextFields = false;
    /** @var null|int */
    private $temporary;
    /** @var bool */
    private $noOffsets = false;
    /** @var bool */
    private $noHighLight = false;
    /** @var bool */
    private $noFields = false;
    /** @var bool */
    private $noFrequencies = false;
    /** @var bool */
    private $skipInitialScan = false;
    /** @var null|array<string> */
    private $stopWords;
    /** @var array<CreateCommandFieldOption> */
    private $fields = [];

    /**
     * @param array<mixed> $arguments
     *
     * @return $this
     */
    public function __call(string $name, array $arguments): self
    {
        $calls = ['set' => true, 'add' => false, 'withAdded' => false, 'with' => false];
        foreach ($calls as $type => $singleArgument) {
            $result = $this->doCall($type, $name, true === $singleArgument ? $arguments[0] ?? null : $arguments);
            if ($result instanceof IndexBuilder) {
                return $result;
            }
        }

        throw new BadMethodCallException(sprintf('Call undefined %s method', $name));
    }

    public function setNoStopWords(): self
    {
        $this->stopWords = [];

        return $this;
    }

    public function withNoStopWords(): self
    {
        $newInstance = clone $this;

        return $newInstance->setNoStopWords();
    }

    public function addField(CreateCommandFieldOption ...$option): self
    {
        array_push($this->fields, ...$option);

        return $this;
    }

    public function create(ClientInterface $client, string $rediSearchVersion = '2.0.0'): ResponseInterface
    {
        $command = $this->getCommand($rediSearchVersion);

        return $client->executeCommand($command);
    }

    /**
     * @throws RuntimeException if the index is not defined
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getCommand(string $rediSearchVersion = '2.0.0'): Create
    {
        if (!is_string($this->index)) {
            throw new RuntimeException('The index name is missing');
        }
        $command = new Create($rediSearchVersion);
        $command
            ->setIndex($this->index)
            ->setMaxTextFields($this->maxTextFields)
            ->setNoOffsets($this->noOffsets)
            ->setNoHighLight($this->noHighLight)
            ->setNoFields($this->noFields)
            ->setNoFrequencies($this->noFrequencies)
            ->setSkipInitialScan($this->skipInitialScan)
        ;
        if (is_string($this->structure)) {
            $command->setStructure($this->structure);
        }
        if (count($this->prefixes) > 0) {
            $command->setPrefixes(...$this->prefixes);
        }
        if (is_string($this->filter)) {
            $command->setFilter($this->filter);
        }
        if (is_string($this->defaultLanguage)) {
            $command->setDefaultLanguage($this->defaultLanguage);
        }
        if (is_string($this->languageField)) {
            $command->setLanguageField($this->languageField);
        }
        if (is_float($this->defaultScore)) {
            $command->setDefaultScore($this->defaultScore);
        }
        if (is_string($this->scoreField)) {
            $command->setScoreField($this->scoreField);
        }
        if (is_string($this->payloadField)) {
            $command->setPayloadField($this->payloadField);
        }
        if (is_int($this->temporary)) {
            $command->setTemporary($this->temporary);
        }
        if (is_array($this->stopWords) && count($this->stopWords) > 0) {
            $command->setStopWords(...$this->stopWords);
        }
        if (is_array($this->stopWords) && 0 === count($this->stopWords)) {
            $command->setNoStopWords();
        }
        foreach ($this->fields as $field) {
            $command->addField($field);
        }

        return $command;
    }

    /**
     * @param array<mixed>|mixed $arguments
     *
     * @return null|$this
     */
    private function doCall(string $type, string $name, $arguments): ?self
    {
        $typeLength = strlen($type);
        if (0 === strpos($name, $type) && $name[$typeLength] === strtoupper($name[$typeLength])) {
            return $this->{$type.'Call'}(substr($name, $typeLength), $arguments);
        }

        return null;
    }

    /**
     * @param mixed $argument
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) -- Called in doCall
     */
    private function withCall(string $name, $argument): self
    {
        $newInstance = clone $this;

        return $newInstance->{'set'.$name}($name, $argument);
    }

    /**
     * @param array<mixed> $arguments
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) -- Called in doCall
     */
    private function withAddedCall(string $name, array $arguments): self
    {
        $newInstance = clone $this;

        return $newInstance->{'add'.$name}($name, $arguments);
    }

    /**
     * @param mixed $argument
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) -- Called in doCall
     */
    private function setCall(string $name, $argument): self
    {
        $propertyName = lcfirst($name);
        if (property_exists($this, $propertyName)) {
            $this->{$propertyName} = $argument;

            return $this;
        }

        throw new BadMethodCallException(sprintf('Call undefined set%s method', $name));
    }

    /**
     * @param array<mixed> $arguments
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) -- Called in doCall
     */
    private function addCall(string $name, array $arguments): self
    {
        $propertyName = lcfirst($name);

        if (!in_array($propertyName, self::IS_ARRAY, true)) {
            throw new BadMethodCallException(sprintf('Call undefined add%s method', $name));
        }

        if (0 === count($arguments)) {
            throw new InvalidArgumentException(sprintf('add%s method need at least one parameter', $name));
        }

        array_push($this->{$propertyName}, ...$arguments);

        return $this;
    }
}
