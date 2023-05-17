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

namespace OpenConext\EngineBlockBundle\Configuration;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Symfony\Component\Translation\TranslatorInterface;

class ErrorFeedbackConfigurationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var TranslatorInterface|m\MockInterface
     */
    private $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = m::mock(TranslatorInterface::class);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function a_link_can_be_queried_for_presence()
    {
        $this->boostrapWikiTranslations([
            'no-session-found' => 'https://support/no-session-found',
            'invalid-response' => 'https://support/invalid-response',
            'not-configured' => 'error_feedback_wiki_links_not-configured',
        ]);

        $errorFeedbackConfiguration = new ErrorFeedbackConfiguration($this->translator);

        $this->assertTrue($errorFeedbackConfiguration->hasWikiLink('no-session-found'));
        $this->assertTrue($errorFeedbackConfiguration->hasWikiLink('invalid-response'));
        $this->assertFalse($errorFeedbackConfiguration->hasWikiLink('not-configured'));
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function a_link_can_be_retrieved()
    {
        $this->boostrapWikiTranslations([
            'no-session-found' => 'https://support/no-session-found',
        ]);

        $errorFeedbackConfiguration = new ErrorFeedbackConfiguration($this->translator);
        $noSessionFound = $errorFeedbackConfiguration->getWikiLink('no-session-found');
        $this->assertSame('https://support/no-session-found', $noSessionFound);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function an_idp_empty_wiki_link_configuration_can_be_provided()
    {
        $this->boostrapWikiTranslations([
            'clock-issue' => '',
        ]);

        $errorFeedbackConfiguration = new ErrorFeedbackConfiguration($this->translator);

        $this->assertFalse($errorFeedbackConfiguration->hasWikiLink('clock-issue'));
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function an_idp_contact_page_can_be_tested()
    {
        $this->boostrapIdpTranslations([
            'clock-issue' => 'Mail',
            'no-session-found' => '',
        ]);

        $errorFeedbackConfiguration = new ErrorFeedbackConfiguration($this->translator);

        $this->assertTrue($errorFeedbackConfiguration->isIdPContactPage('clock-issue'));
        $this->assertFalse($errorFeedbackConfiguration->isIdPContactPage('no-session-found'));

        $this->assertSame('Mail', $errorFeedbackConfiguration->getIdpContactShortLabel('clock-issue'));
        $this->assertSame('', $errorFeedbackConfiguration->getIdpContactShortLabel('no-session-found'));
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function an_idp_empty_contact_page_configuration_can_provided()
    {
        $this->boostrapIdpTranslations([
            'clock-issue' => '',
            'no-session-found' => '',
        ]);

        $errorFeedbackConfiguration = new ErrorFeedbackConfiguration($this->translator);

        $this->assertFalse($errorFeedbackConfiguration->isIdPContactPage('clock-issue'));
        $this->assertFalse($errorFeedbackConfiguration->isIdPContactPage('no-session-found'));
    }

    private function boostrapWikiTranslations(array $translations)
    {
        foreach ($translations as $key => $value) {
            $key = 'error_feedback_wiki_links_' . $key;
            $this->translator->shouldReceive('trans')
                ->with($key)->andReturn($value);
        }
    }


    private function boostrapIdpTranslations(array $translations)
    {
        foreach ($translations as $key => $value) {
            $key = 'error_feedback_idp_contact_label_small_' . $key;
            $this->translator->shouldReceive('trans')
                ->with($key)->andReturn($value);
        }
    }
}
