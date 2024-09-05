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

namespace OpenConext\EngineBlock\Service;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReleaseAsEnforcerTest extends TestCase
{
    private $logger;
    private $enforcer;

    protected function setUp(): void
    {
        $this->logger = m::mock(LoggerInterface::class);
        $this->enforcer = new ReleaseAsEnforcer($this->logger);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider enforceDataProvider
     */
    public function testEnforce($attributes, $releaseAsOverrides, $expectedResult, $expectedLogMessages)
    {
        foreach ($expectedLogMessages as $message) {
            $this->logger->shouldReceive('notice')->once()->with($message);
        }

        $result = $this->enforcer->enforce($attributes, $releaseAsOverrides);

        $this->assertEquals($expectedResult, $result);

        foreach ($releaseAsOverrides as $oldName => $override) {
            $this->assertArrayNotHasKey($oldName, $result);
        }
    }


    /**
     * @dataProvider enforceDataProviderWarnings
     */
    public function testEnforceImpossible($attributes, $releaseAsOverrides, $expectedResult, $expectedLogMessage)
    {
        $this->logger->shouldReceive('warning')->with($expectedLogMessage);
        $result = $this->enforcer->enforce($attributes, $releaseAsOverrides);
        $this->assertEquals($expectedResult, $result);
    }

    public function enforceDataProvider()
    {
        return [
            'single attribute override' => [
                'attributes' => [
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:cn" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:sn" => ["Doe"],
                    "urn:mace:dir:attribute-def:givenName" => ["Ad"],
                    "urn:mace:dir:attribute-def:mail" => ["ad@example.com"]
                ],
                'releaseAsOverrides' => [
                    "urn:mace:dir:attribute-def:cn" => [
                        [
                            "value" => "*",
                            "release_as" => "ComonNaam",
                            "use_as_nameid" => false
                        ]
                    ]
                ],
                'expectedResult' => [
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:sn" => ["Doe"],
                    "urn:mace:dir:attribute-def:givenName" => ["Ad"],
                    "urn:mace:dir:attribute-def:mail" => ["ad@example.com"],
                    "ComonNaam" => ["Ad Doe"]
                ],
                'expectedLogMessages' => [
                    'Releasing attribute "urn:mace:dir:attribute-def:cn" as "ComonNaam" as specified in the release_as ARP setting'
                ]
            ],
            'single attribute override, empty attribute value is allowed' => [
                'attributes' => [
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:cn" => [],
                    "urn:mace:dir:attribute-def:sn" => ["Doe"],
                    "urn:mace:dir:attribute-def:givenName" => ["Ad"],
                    "urn:mace:dir:attribute-def:mail" => ["ad@example.com"]
                ],
                'releaseAsOverrides' => [
                    "urn:mace:dir:attribute-def:cn" => [
                        [
                            "value" => "*",
                            "release_as" => "ComonNaam",
                            "use_as_nameid" => false
                        ]
                    ]
                ],
                'expectedResult' => [
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:sn" => ["Doe"],
                    "urn:mace:dir:attribute-def:givenName" => ["Ad"],
                    "urn:mace:dir:attribute-def:mail" => ["ad@example.com"],
                    "ComonNaam" => []
                ],
                'expectedLogMessages' => [
                    'Releasing attribute "urn:mace:dir:attribute-def:cn" as "ComonNaam" as specified in the release_as ARP setting'
                ]
            ],
            'multiple attribute overrides' => [
                'attributes' => [
                    "urn:mace:dir:attribute-def:displayName" => ["John Smith"],
                    "urn:mace:dir:attribute-def:cn" => ["John Smith"],
                    "urn:mace:dir:attribute-def:sn" => ["Smith"],
                    "urn:mace:dir:attribute-def:givenName" => ["John"],
                    "urn:mace:dir:attribute-def:mail" => ["john@example.com"],
                    "urn:mace:dir:attribute-def:eduPersonAffiliation" => ["student", "member"]
                ],
                'releaseAsOverrides' => [
                    "urn:mace:dir:attribute-def:cn" => [
                        [
                            "value" => "*",
                            "release_as" => "FullName",
                            "use_as_nameid" => false
                        ]
                    ],
                    "urn:mace:dir:attribute-def:eduPersonAffiliation" => [
                        [
                            "value" => "*",
                            "release_as" => "Affiliation",
                            "use_as_nameid" => false
                        ]
                    ]
                ],
                'expectedResult' => [
                    "urn:mace:dir:attribute-def:displayName" => ["John Smith"],
                    "urn:mace:dir:attribute-def:sn" => ["Smith"],
                    "urn:mace:dir:attribute-def:givenName" => ["John"],
                    "urn:mace:dir:attribute-def:mail" => ["john@example.com"],
                    "FullName" => ["John Smith"],
                    "Affiliation" => ["student", "member"]
                ],
                'expectedLogMessages' => [
                    'Releasing attribute "urn:mace:dir:attribute-def:cn" as "FullName" as specified in the release_as ARP setting',
                    'Releasing attribute "urn:mace:dir:attribute-def:eduPersonAffiliation" as "Affiliation" as specified in the release_as ARP setting'
                ]
            ],
            'no overrides, result in no attributes being changed' => [
                'attributes' => [
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:cn" => ["Ad Doe"],
                ],
                'releaseAsOverrides' => [],
                'expectedResult' => [
                    "urn:mace:dir:attribute-def:cn" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                ],
                'expectedLogMessages' => [
                ]
            ],
            'targeted attribute not in assertion' => [
                'attributes' => [
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:cn" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:sn" => ["Doe"],
                    "urn:mace:dir:attribute-def:givenName" => ["Ad"],
                    "urn:mace:dir:attribute-def:mail" => ["ad@example.com"],
                ],
                'releaseAsOverrides' => [
                    "urn:mace:dir:attribute-def:eduPersonTargetedId" => [
                        [
                            "value" => "*",
                            "release_as" => "UserName",
                            "use_as_nameid" => false
                        ]
                    ]
                ],
                'expectedResult' => [
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:cn" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:sn" => ["Doe"],
                    "urn:mace:dir:attribute-def:givenName" => ["Ad"],
                    "urn:mace:dir:attribute-def:mail" => ["ad@example.com"]
                ],
                'expectedLogMessages' => ['Releasing "urn:mace:dir:attribute-def:eduPersonTargetedId" as "UserName" is not possible, "urn:mace:dir:attribute-def:eduPersonTargetedId" is not in assertion']
            ],
        ];
    }

    public function enforceDataProviderWarnings()
    {
        return [
            'targeted attribute value is set to null in assertion' => [
                'attributes' => [
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:cn" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:sn" => ["Doe"],
                    "urn:mace:dir:attribute-def:eduPersonTargetedId" => null,
                    "urn:mace:dir:attribute-def:givenName" => ["Ad"],
                    "urn:mace:dir:attribute-def:mail" => ["ad@example.com"],
                ],
                'releaseAsOverrides' => [
                    "urn:mace:dir:attribute-def:eduPersonTargetedId" => [
                        [
                            "value" => "*",
                            "release_as" => "UserName",
                            "use_as_nameid" => false
                        ]
                    ]
                ],
                'expectedResult' => [
                    "urn:mace:dir:attribute-def:displayName" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:cn" => ["Ad Doe"],
                    "urn:mace:dir:attribute-def:sn" => ["Doe"],
                    "urn:mace:dir:attribute-def:givenName" => ["Ad"],
                    "urn:mace:dir:attribute-def:mail" => ["ad@example.com"]
                ],
                'expectedLogMessages' => 'Releasing "urn:mace:dir:attribute-def:eduPersonTargetedId" as "UserName" is not possible, value for "urn:mace:dir:attribute-def:eduPersonTargetedId" is null'
            ],
        ];
    }
}
