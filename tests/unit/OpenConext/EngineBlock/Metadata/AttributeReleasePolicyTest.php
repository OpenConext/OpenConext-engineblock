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

use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Error;

class AttributeReleasePolicyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSimple()
    {
        $policy = new AttributeReleasePolicy(
            array(
                'a'=>array('*'),
                'b'=>array('b'),
                'c'=>array('c*'),
                'd'=>array('*d'),
            )
        );
        $this->assertEquals(array('a', 'b', 'c', 'd'), $policy->getAttributeNames());
        $this->assertTrue($policy->hasAttribute('a'));
        $this->assertFalse($policy->hasAttribute('z'));
        $this->assertTrue($policy->isAllowed('a', 'a'));
        $this->assertTrue($policy->isAllowed('b', 'b'));
        $this->assertFalse($policy->isAllowed('b', 'babe'));
        $this->assertTrue($policy->isAllowed('c', 'cat'));
        $this->assertFalse($policy->isAllowed('c', 'dad'));
        // Wildcard matching at the end only:
        $this->assertFalse($policy->isAllowed('d', 'tricked'));
    }

    public function testEmptyInstantiation()
    {
        $policy = new AttributeReleasePolicy(array());
        $this->assertEmpty($policy->getAttributeNames());
        $this->assertFalse($policy->isAllowed('a', 'a'));
    }

    public function testInvalidInstantiation()
    {
        $e = null;
        try {
            new AttributeReleasePolicy(array('a'=>'b'));
        } catch (InvalidArgumentException $e) {
        }
        $this->assertNotNull($e);

        $e = null;
        try {
            new AttributeReleasePolicy(array(array('b')));
        } catch (InvalidArgumentException $e) {
        }
        $this->assertNotNull($e);

        $e = null;
        try {
            new AttributeReleasePolicy(array('a'=>array(1)));
        } catch (InvalidArgumentException $e) {
        }
        $this->assertNotNull($e);
    }

    public function testArpWithSources()
    {
        $policy = new AttributeReleasePolicy(
            array(
                'a' => array('a'),
                'b' => array(
                    array(
                        'value' => 'b',
                        'source' => 'b',
                    ),
                ),
            )
        );

        $this->assertEquals(array('a', 'b'), $policy->getAttributeNames());
        $this->assertTrue($policy->hasAttribute('a'));
        $this->assertTrue($policy->hasAttribute('b'));
        $this->assertTrue($policy->isAllowed('a', 'a'));
        $this->assertTrue($policy->isAllowed('b', 'b'));
        $this->assertFalse($policy->isAllowed('a', 'b'));
        $this->assertFalse($policy->isAllowed('b', 'a'));
    }

    public function testInvalidArpWithSourceSpecification()
    {
        $this->expectException(InvalidArgumentException::class);

        new AttributeReleasePolicy(
            array(
                'b' => array(
                    array(
                        'source' => 'b',
                    ),
                ),
            )
        );
    }

    public function testAttributesEligibleForAggregation()
    {
        $policy = new AttributeReleasePolicy(
            array(
                'a' => array('a'),
                'b' => array(
                    array(
                        'value' => 'b',
                        'source' => 'b',
                    ),
                ),
                'c' => array(
                    array(
                        'value' => 'c',
                    ),
                ),
            )
        );

        $this->assertEquals(
            array(
                'b' => array(
                    array(
                        'value' => 'b',
                        'source' => 'b',
                    ),
                ),
            ),
            $policy->getRulesWithSourceSpecification()
        );
    }
    public function testGetSource()
    {
        $policy = new AttributeReleasePolicy(
            array(
                'a' => array('a'),
                'b' => array(
                    array(
                        'value' => 'b',
                        'source' => 'b',
                    ),
                ),
                'c' => array(
                    array(
                        'value' => 'c',
                    ),
                ),
            )
        );

        $this->assertEquals('idp', $policy->getSource('a'), 'Default source should equal idp');
        $this->assertEquals('b', $policy->getSource('b'));
        $this->assertEquals('idp', $policy->getSource('c'), 'Default source should equal idp');
    }
    public function testGetRulesWithReleaseAsSpecification()
    {
        $policy = new AttributeReleasePolicy([
            'attr1' => [
                ['value' => 'arbitrary', 'release_as' => 'Noot'],
            ],
            'attr2' => [
                ['value' => 'arbitrary', 'release_as' => 'Mies'],
            ],
            'attr3' => [
                ['value' => 'arbitrary'],
            ],
        ]);

        $expected = [
            'attr1' => [
                ['value' => 'arbitrary', 'release_as' => 'Noot'],
            ],
            'attr2' => [
                ['value' => 'arbitrary', 'release_as' => 'Mies'],
            ],
        ];

        $this->assertEquals($expected, $policy->getRulesWithReleaseAsSpecification());
    }

    public function testFindNameIdSubstitute()
    {
        $policy = new AttributeReleasePolicy([
            'attr1' => [
                ['value' => 'value1', 'use_as_nameid' => false],
            ],
            'attr2' => [
                ['value' => 'value2', 'use_as_nameid' => true],
            ],
            'attr3' => [
                ['value' => 'value3', 'use_as_nameid' => false],
            ],
        ]);

        $this->assertEquals('attr2', $policy->findNameIdSubstitute());

        $policyWithoutNameId = new AttributeReleasePolicy([
            'attr1' => [['value' => 'value1', 'use_as_nameid' => false]],
            'attr2' => [['value' => 'value2', 'use_as_nameid' => false]],
        ]);

        $this->assertNull($policyWithoutNameId->findNameIdSubstitute());
    }
    public function testFindNameIdSubstituteWithReleaseAs()
    {
        $policy = new AttributeReleasePolicy([
            'attr1' => [
                ['value' => 'value1', 'use_as_nameid' => false],
            ],
            'attr2' => [
                // When release_as is set, the name id value must be retrieved on that
                // attribute in the assertion (release as is evaluated first)
                ['value' => 'value2', 'use_as_nameid' => true, 'release_as' => 'new_attr_name'],
            ],
            'attr3' => [
                ['value' => 'value3', 'use_as_nameid' => false],
            ],
        ]);
        $this->assertEquals('new_attr_name', $policy->findNameIdSubstitute());
    }
}
