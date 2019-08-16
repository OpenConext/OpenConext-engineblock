<?php

/**
 * Copyright 2014 SURFnet B.V.
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
use PHPUnit_Framework_Error;
use PHPUnit_Framework_TestCase;

/**
 * Class AttributeReleasePolicy
 * @package OpenConext\EngineBlock\Metadata
 */
class AttributeReleasePolicyTest extends PHPUnit_Framework_TestCase
{
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
}
