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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Controllers;

use OpenConext\EngineBlockFunctionalTestingBundle\Helper\TestEntitySeeder;
use SAML2\XML\saml\NameID;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class ConsentController
{

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(
        Twig_Environment $twig
    ) {
        $this->twig = $twig;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function consentAction(Request $request)
    {
        $processConsentUrl = '#';
        $fakeResponseId = '918723649';
        $fakeSp = TestEntitySeeder::buildSp();
        $fakeIdP = TestEntitySeeder::buildIdP();
        $supportContact = 'Helpdesk';
        $nameId = new NameID();
        $nameId->setValue('user@openconext');
        $profileUrl = 'profile.openconext.org';
        $attributes = [
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid' => ['joe-f12'],
            'urn:mace:dir:attribute-def:cn' => ['John Doe'],
            'urn:mace:dir:attribute-def:sn' => ['Doe'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'urn:mace:dir:attribute-def:isMemberOf' => ['urn:collab:org:vm.openconext.org'],
        ];
        $attributeMotivations = [
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => 'Test  tooltip',
            'urn:mace:dir:attribute-def:givenName' => 'Test tooltip',
        ];

        return new Response($this->twig->render('@theme/Authentication/View/Proxy/consent.html.twig', [
            'action' => $processConsentUrl,
            'responseId' => $fakeResponseId,
            'sp' => $fakeSp,
            'idp' => $fakeIdP,
            'idpSupport' => $supportContact,
            'attributes' => $attributes,
            'attributeSources' => [],
            'attributeMotivations' => $attributeMotivations,
            'minimalConsent' => $fakeIdP->getConsentSettings()->isMinimal($fakeSp->entityId),
            'consentCount' => 5,
            'nameId' => $nameId,
            'nameIdIsPersistent' => true,
            'profileUrl' => $profileUrl,
            'showConsentExplanation' => $fakeIdP->getConsentSettings()->hasConsentExplanation($fakeSp->entityId),
            'consentSettings' => $fakeIdP->getConsentSettings(),
            'spEntityId' => $fakeSp->entityId,
            'hideHeader' => true,
            'hideFooter' => true,
        ]), 200);
    }
}
