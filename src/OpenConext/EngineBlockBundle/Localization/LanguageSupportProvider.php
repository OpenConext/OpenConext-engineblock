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

namespace OpenConext\EngineBlockBundle\Localization;

use OpenConext\EngineBlockBundle\Exception\UnsupportedLanguageException;

class LanguageSupportProvider
{
    /**
     * @var string[]
     */
    private $supportedLanguages;

    /**
     * @param string[] $availableLanguages
     * @param string[] $enabledLanguages
     */
    public function __construct(array $availableLanguages, array $enabledLanguages)
    {
        $languages = [];
        foreach ($enabledLanguages as $language) {
            if (in_array($language, $availableLanguages)) {
                $languages[$language] = $language;
            } else {
                throw new UnsupportedLanguageException(
                    sprintf("Unable to activate unsupported language '%s', please check your configuration", $language)
                );
            }
        }

        if (empty($languages)) {
            throw new UnsupportedLanguageException('No active languages are configured, please check your configuration');
        }

        $this->supportedLanguages = array_values($languages);
    }

    /**
     * @return string[]
     */
    public function getSupportedLanguages()
    {
        return $this->supportedLanguages;
    }
}
