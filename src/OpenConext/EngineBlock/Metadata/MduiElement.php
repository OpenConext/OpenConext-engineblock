<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Metadata;

use Assert\Assertion;
use JsonSerializable;
use OpenConext\EngineBlock\Exception\MduiNotFoundException;

class MduiElement implements MultilingualElement, JsonSerializable
{
    /** @var string */
    private $name;

    /** @var MultilingualValue[] */
    private $values;

    public function __construct(string $name, array $values)
    {
        Assertion::allIsInstanceOf(
            $values,
            MultilingualValue::class,
            'The \'values\' must all be of type: MultilingualValue'
        );

        $this->name = $name;
        $this->setValues($values);
    }

    public static function fromJson(array $multiLingualElement): MultilingualElement
    {
        if (!array_key_exists('name', $multiLingualElement)) {
            throw new MduiRuntimeException('Unable to create MduiElement without a name');
        }
        $values = [];
        if (array_key_exists('values', $multiLingualElement)) {
            foreach ($multiLingualElement['values'] as $multiLinguaValue) {
                if (array_key_exists('value', $multiLinguaValue) && array_key_exists('language', $multiLinguaValue)) {
                    $values[$multiLinguaValue['language']] = new MultilingualValue(
                        $multiLinguaValue['value'],
                        $multiLinguaValue['language']
                    );
                }
            }
            return new self($multiLingualElement['name'], $values);
        }
        return new EmptyMduiElement($multiLingualElement);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function translate(string $language): MultilingualValue
    {
        if (!array_key_exists($language, $this->values)) {
            throw new MduiNotFoundException(
                sprintf(
                    'Unable to find the element value named: %s for language \'%s\'',
                    $this->name,
                    $language
                )
            );
        }
        return $this->values[$language];
    }

    private function setValues(array $values): void
    {
        /** @var MultilingualValue $value */
        foreach ($values as $value) {
            $this->values[$value->getLanguage()] = $value;
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'values' => $this->values,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getConfiguredLanguages(): array
    {
        return array_keys($this->values);
    }
}
