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

namespace OpenConext\EngineBlockBundle\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response;
use OpenConext\EngineBlockBundle\Pdp\PolicyDecision;
use PHPUnit\Framework\TestCase;

class PolicyDecisionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group Pdp
     *
     * @dataProvider pdpResponseAndExpectedPermissionProvider
     * @param $responseName
     * @param $expectedPermission
     */
    public function the_correct_policy_decision_should_be_made_based_on_a_pdp_response(
        $responseName,
        $expectedPermission
    ) {
        $responseJson = json_decode(file_get_contents(__DIR__ . '/fixture/response_' . $responseName . '.json'), true);
        $response = Response::fromData($responseJson);

        $decision = PolicyDecision::fromResponse($response);

        $this->assertEquals($expectedPermission, $decision->permitsAccess());
    }

    /**
     * @test
     * @group Pdp
     */
    public function a_deny_policys_localized_messages_are_parsed_correctly()
    {
        $responseJson = json_decode(file_get_contents(__DIR__ . '/fixture/response_deny.json'), true);
        $response = Response::fromData($responseJson);

        $decision = PolicyDecision::fromResponse($response);

        $expectedDenyMessageEn = 'Students do not have access to this resource';
        $expectedDenyMessageNl = 'Studenten hebben geen toegang tot deze dienst';

        $denyMessageEn = $decision->getLocalizedDenyMessage('en');
        $denyMessageNl = $decision->getLocalizedDenyMessage('nl');

        $this->assertEquals($expectedDenyMessageEn, $denyMessageEn);
        $this->assertEquals($expectedDenyMessageNl, $denyMessageNl);
    }

    /**
     * @test
     * @group Pdp
     */
    public function a_deny_policys_localized_deny_message_correctly_falls_back_to_the_default_locale_if_the_given_locale_was_not_found()
    {
        $responseJson = json_decode(file_get_contents(__DIR__ . '/fixture/response_deny.json'), true);
        $response = Response::fromData($responseJson);

        $decision = PolicyDecision::fromResponse($response);

        $expectedFallbackDenyMessage = 'Students do not have access to this resource';

        $fallbackDenyMessage = $decision->getLocalizedDenyMessage('de', 'en');

        $this->assertEquals($expectedFallbackDenyMessage, $fallbackDenyMessage);
        $this->assertNull($decision->getIdpLogo());

    }

    /**
     * @test
     * @group Pdp
     */
    public function a_deny_policy_with_idp_specific_message_is_parsed_correctly()
    {
        $responseJson = json_decode(file_get_contents(__DIR__ . '/fixture/response_deny_idp_specific.json'), true);
        $response = Response::fromData($responseJson);

        $logo = new Logo('logo.png');

        $decision = PolicyDecision::fromResponse($response);
        $decision->setIdpLogo($logo);

        $expectedDenyMessageEn = 'MyIdp students do not have access to the Foobar portal';
        $expectedDenyMessageNl = 'MyIdp studenten hebben geen toegang tot het Foobar portaal';

        $denyMessageEn = $decision->getLocalizedDenyMessage('en');
        $denyMessageNl = $decision->getLocalizedDenyMessage('nl');

        $this->assertEquals($expectedDenyMessageEn, $denyMessageEn);
        $this->assertEquals($expectedDenyMessageNl, $denyMessageNl);

        $this->assertEquals($logo, $decision->getIdpLogo());

    }

    /**
     * @test
     * @group Pdp
     */
    public function an_indeterminate_policys_status_message_is_acquired_correctly()
    {
        $responseJson = json_decode(file_get_contents(__DIR__ . '/fixture/response_indeterminate.json'), true);
        $response = Response::fromData($responseJson);

        $logo = new Logo('logo.png');

        $decision = PolicyDecision::fromResponse($response);
        $decision->setIdpLogo($logo);

        $expectedStatusMessage = 'Missing required attribute';

        $statusMessage = $decision->getStatusMessage();

        $this->assertEquals($expectedStatusMessage, $statusMessage);
    }

    /**
     * @test
     * @group Pdp
     */
    public function a_status_message_cannot_be_acquired_from_a_policy_that_has_none()
    {
        $this->expectException('\OpenConext\EngineBlock\Exception\RuntimeException');
        $this->expectExceptionMessage('No status message found');

        $responseJson = json_decode(file_get_contents(__DIR__ . '/fixture/response_deny.json'), true);
        $response = Response::fromData($responseJson);

        $decision = PolicyDecision::fromResponse($response);
        $decision->getStatusMessage();
    }

    /**
     * @test
     * @group Pdp
     */
    public function a_localized_deny_message_cannot_be_acquired_if_the_chosen_and_the_default_locale_are_not_present()
    {
        $nonPresentLocale = 'de';
        $nonPresentDefaultLocale = 'fr';

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage(sprintf(
            'No localized deny message for locale "%s" or default locale "%s" found',
            $nonPresentLocale,
            $nonPresentDefaultLocale
        ));

        $responseJson = json_decode(file_get_contents(__DIR__ . '/fixture/response_deny.json'), true);
        $response = Response::fromData($responseJson);

        $decision = PolicyDecision::fromResponse($response);
        $decision->getLocalizedDenyMessage($nonPresentLocale, $nonPresentDefaultLocale);
    }

    /**
     * @test
     * @group Pdp
     */
    public function a_localized_deny_message_cannot_be_acquired_from_a_policy_decision_that_does_not_have_one()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('No localized deny messages present');

        $responseJson = json_decode(file_get_contents(__DIR__ . '/fixture/response_permit.json'), true);
        $response = Response::fromData($responseJson);

        $decision = PolicyDecision::fromResponse($response);
        $decision->getLocalizedDenyMessage('en');
    }

    public function pdpResponseAndExpectedPermissionProvider()
    {
        return [
            'Deny response does not permit access' => ['deny', false],
            'Indeterminate response does not permit access' => ['indeterminate', false],
            'Not applicable response permits access' => ['not_applicable', true],
            'Permit response permits access' => ['permit', true],
        ];
    }
}
