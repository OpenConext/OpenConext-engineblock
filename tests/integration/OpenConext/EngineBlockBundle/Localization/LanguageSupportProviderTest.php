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
use PHPUnit\Framework\TestCase;

class LanguageSupportProviderTest extends TestCase
{
    /**
     * @test
     */
    public function only_enabled_languages_should_be_supported()
    {
        $LanguageSupportProvider = new LanguageSupportProvider(['nl', 'en', 'pt'], ['nl', 'en']);

        $this->assertSame(['nl', 'en'], $LanguageSupportProvider->getSupportedLanguages());
    }

    /**
     * @test
     */
    public function duplicate_language_entries_should_not_result_in_duplicate_entries_with_supported_languages()
    {
        $LanguageSupportProvider = new LanguageSupportProvider(['nl', 'en', 'pt', 'nl'], ['nl', 'en', 'nl']);

        $this->assertSame(['nl', 'en'], $LanguageSupportProvider->getSupportedLanguages());
    }

    /**
     * @test
     */
    public function an_unsupported_language_should_throw_an_exception()
    {
        $this->expectException(UnsupportedLanguageException::class);
        $this->expectExceptionMessage('A supported language is enabled while not available');

        $LanguageSupportProvider = new LanguageSupportProvider(['nl', 'en', 'pt'], ['de']);
    }

    /**
     * @test
     */
    public function no_setting_enabled_languages_should_result_in_an_exception()
    {
        $this->expectException(UnsupportedLanguageException::class);
        $this->expectExceptionMessage('No supported languages found');

        $LanguageSupportProvider = new LanguageSupportProvider(['nl', 'en', 'pt'], []);
    }

    /**
     * @test
     */
    public function no_active_languages_should_result_in_an__excpetion()
    {
        $this->expectException(UnsupportedLanguageException::class);
        $this->expectExceptionMessage('A supported language is enabled while not available');

        $LanguageSupportProvider = new LanguageSupportProvider([], ['nl', 'en']);
    }
}
