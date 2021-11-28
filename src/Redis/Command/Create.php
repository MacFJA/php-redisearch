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

use MacFJA\RediSearch\Redis\Command\Option\CustomValidatorOption as CV;
use MacFJA\RediSearch\Redis\Command\Option\FlagOption;
use MacFJA\RediSearch\Redis\Command\Option\NamedOption;
use MacFJA\RediSearch\Redis\Command\Option\NamelessOption;
use MacFJA\RediSearch\Redis\Command\Option\NotEmptyOption;
use MacFJA\RediSearch\Redis\Command\Option\NumberedOption;

class Create extends AbstractCommand
{
    use LanguageOptionTrait;
    use AddFieldOptionTrait;

    public function __construct(string $rediSearchVersion = self::MIN_IMPLEMENTED_VERSION)
    {
        parent::__construct([
            'index' => new NamelessOption(null, '>=2.0.0'),
            'structure' => [
                CV::allowedValues(new NamedOption('ON', null, '>=2.0.0 <2.2.0'), ['HASH']),
                CV::allowedValues(new NamedOption('ON', null, '>=2.2.0'), ['HASH', 'JSON']),
            ],
            'prefixes' => new NotEmptyOption(new NumberedOption('PREFIX', null, '>=2.0.0')),
            'filter' => new NamedOption('FILTER', null, '>=2.0.0'),
            'default_lang' => $this->getLanguageOptions(),
            'lang_field' => new NamedOption('LANGUAGE_FIELD', null, '>=2.0.0'),
            'default_score' => CV::isNumeric(new NamedOption('SCORE', null, '>=2.0.0')),
            'score_field' => new NamedOption('SCORE_FIELD', null, '>=2.0.0'),
            'payload_field' => new NamedOption('PAYLOAD_FIELD', null, '>=2.0.0'),
            'max_text_fields' => new FlagOption('MAXTEXTFIELDS', false, '>=2.0.0'),
            'temporary' => CV::isNumeric(new NamedOption('TEMPORARY', null, '>=2.0.0')),
            'no_offsets' => new FlagOption('NOOFFSETS', false, '>=2.0.0'),
            'no_hl' => new FlagOption('NOHL', false, '>=2.0.0'),
            'no_fields' => new FlagOption('NOFIELDS', false, '>=2.0.0'),
            'no_freqs' => new FlagOption('NOFREQS', false, '>=2.0.0'),
            'skip_initial_scan' => new FlagOption('SKIPINITIALSCAN', false, '>=2.0.0'),
            'stopwords' => new NumberedOption('STOPWORDS', null, '>=2.0.0'),
            'schema' => new FlagOption('SCHEMA', true, '>=2.0.0'),
            'fields' => [],
        ], $rediSearchVersion);
    }

    public function setIndex(string $index): self
    {
        $this->options['index']->setValue($index);

        return $this;
    }

    public function setStructure(string $structureType = 'HASH'): self
    {
        array_walk($this->options['structure'], static function (CV $option) use ($structureType): void {
            $option->setValue($structureType);
        });

        return $this;
    }

    public function setPrefixes(string ...$prefix): self
    {
        $this->options['prefixes']->setArguments($prefix);

        return $this;
    }

    public function setFilter(string $filter): self
    {
        $this->options['filter']->setValue($filter);

        return $this;
    }

    public function setDefaultLanguage(string $language): self
    {
        array_walk($this->options['default_lang'], static function (CV $option) use ($language): void {
            $option->setValue($language);
        });

        return $this;
    }

    public function setLanguageField(string $fieldName): self
    {
        $this->options['lang_field']->setValue($fieldName);

        return $this;
    }

    public function setDefaultScore(float $score): self
    {
        $this->options['default_score']->setValue($score);

        return $this;
    }

    public function setScoreField(string $fieldName): self
    {
        $this->options['score_field']->setValue($fieldName);

        return $this;
    }

    public function setPayloadField(string $fieldName): self
    {
        $this->options['payload_field']->setValue($fieldName);

        return $this;
    }

    public function setMaxTextFields(bool $active = true): self
    {
        $this->options['max_text_fields']->setActive($active);

        return $this;
    }

    public function setTemporary(int $seconds): self
    {
        $this->options['temporary']->setValue($seconds);

        return $this;
    }

    public function setNoOffsets(bool $active = true): self
    {
        $this->options['no_offsets']->setActive($active);

        return $this;
    }

    public function setNoHighLight(bool $active = true): self
    {
        $this->options['no_hl']->setActive($active);

        return $this;
    }

    public function setNoFields(bool $active = true): self
    {
        $this->options['no_fields']->setActive($active);

        return $this;
    }

    public function setNoFrequencies(bool $active = true): self
    {
        $this->options['no_freqs']->setActive($active);

        return $this;
    }

    public function setSkipInitialScan(bool $active = true): self
    {
        $this->options['skip_initial_scan']->setActive($active);

        return $this;
    }

    public function setStopWords(string ...$stopWords): self
    {
        $this->options['stopwords']->setArguments($stopWords);

        return $this;
    }

    public function setNoStopWords(): self
    {
        $this->options['stopwords']->setArguments([]);

        return $this;
    }

    public function getId(): string
    {
        return 'FT.CREATE';
    }

    protected function getRequiredOptions(): array
    {
        return ['index', 'schema', 'fields'];
    }
}
