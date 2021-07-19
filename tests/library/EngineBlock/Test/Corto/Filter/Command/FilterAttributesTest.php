<?php

/**
 * Copyright 2021 Stichting Kennisnet
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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use PHPUnit\Framework\TestCase;

class EngineBlock_Test_Corto_Filter_Command_FilterAtributesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    const EB_FEATURE_FILTER_ATTRIBUTES = 'eb.feature_filter_attributes';
    const FILTER_ATTRIBUTE = "filterAttribute";
    const ADDITIONAL_FILTER_ATTRIBUTE = "additional:filter:attribute";
    const DO_NOT_FILTER_ATTRIBUTE = "doNotFilterAttribute";
    const ATTRIBUTE_VALUE = array("attribute value");

    /**
     * @var EngineBlock_Corto_Filter_Command_FilterAttributes
     */
    private $filter_Command_FilterAttributes;

    /**
     * @var FeatureConfiguration
     */
    private $featureConfiguration;

    public function setUp()
    {
        $this->featureConfiguration = Phake::mock(FeatureConfiguration::class);
        $filterAttributes = array(self::FILTER_ATTRIBUTE, self::ADDITIONAL_FILTER_ATTRIBUTE);

        $this->filter_Command_FilterAttributes = new EngineBlock_Corto_Filter_Command_FilterAttributes(
            $this->featureConfiguration, $filterAttributes);

        Phake::when($this->featureConfiguration)
            ->isEnabled(self::EB_FEATURE_FILTER_ATTRIBUTES)
            ->thenReturn(true);
    }

    public function testFilterAttributes()
    {
        $this->filter_Command_FilterAttributes
            ->setResponseAttributes(array(self::DO_NOT_FILTER_ATTRIBUTE => self::ATTRIBUTE_VALUE,
                self::FILTER_ATTRIBUTE => self::ATTRIBUTE_VALUE,
                self::ADDITIONAL_FILTER_ATTRIBUTE => self::ATTRIBUTE_VALUE));

        $this->filter_Command_FilterAttributes->execute();

        $responseAttributes = $this->filter_Command_FilterAttributes->getResponseAttributes();
        $this->assertArrayNotHasKey(self::FILTER_ATTRIBUTE, $responseAttributes);
        $this->assertArrayNotHasKey(self::ADDITIONAL_FILTER_ATTRIBUTE, $responseAttributes);
        $this->assertArrayHasKey(self::DO_NOT_FILTER_ATTRIBUTE, $responseAttributes);
        $this->assertEquals(1, sizeof($responseAttributes));
    }

    public function testDoNotFilterAttributes()
    {
        $this->filter_Command_FilterAttributes
            ->setResponseAttributes(array(self::DO_NOT_FILTER_ATTRIBUTE => self::ATTRIBUTE_VALUE,
                self::FILTER_ATTRIBUTE => self::ATTRIBUTE_VALUE,
                self::ADDITIONAL_FILTER_ATTRIBUTE => self::ATTRIBUTE_VALUE));

        Phake::when($this->featureConfiguration)
            ->isEnabled(self::EB_FEATURE_FILTER_ATTRIBUTES)
            ->thenReturn(false);

        $this->filter_Command_FilterAttributes->execute();

        $responseAttributes = $this->filter_Command_FilterAttributes->getResponseAttributes();
        $this->assertArrayHasKey(self::FILTER_ATTRIBUTE, $responseAttributes);
        $this->assertArrayHasKey(self::ADDITIONAL_FILTER_ATTRIBUTE, $responseAttributes);
        $this->assertArrayHasKey(self::DO_NOT_FILTER_ATTRIBUTE, $responseAttributes);
        $this->assertEquals(3, sizeof($responseAttributes));
    }

    public function testDoNotCrashOnMissingFilterAttribute()
    {
        $this->filter_Command_FilterAttributes
            ->setResponseAttributes(array(self::DO_NOT_FILTER_ATTRIBUTE => self::ATTRIBUTE_VALUE,
                self::ADDITIONAL_FILTER_ATTRIBUTE => self::ATTRIBUTE_VALUE));

        $this->filter_Command_FilterAttributes->execute();

        $responseAttributes = $this->filter_Command_FilterAttributes->getResponseAttributes();
        $this->assertArrayNotHasKey(self::FILTER_ATTRIBUTE, $responseAttributes);
        $this->assertArrayNotHasKey(self::ADDITIONAL_FILTER_ATTRIBUTE, $responseAttributes);
        $this->assertArrayHasKey(self::DO_NOT_FILTER_ATTRIBUTE, $responseAttributes);
        $this->assertEquals(1, sizeof($responseAttributes));
    }
}