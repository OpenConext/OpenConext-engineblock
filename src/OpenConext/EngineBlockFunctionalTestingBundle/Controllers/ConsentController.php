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
use SAML2\Constants;
use SAML2\XML\saml\NameID;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;
use function base64_encode;

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

    public function sendAction()
    {
        $action = '#';
        $encodedMessage = base64_encode('the encoded message, no problem this is bogus on the test endpoint');
        return new Response($this->twig->render('@theme/Authentication/View/Proxy/form.html.twig', [
            'action' => $action,
            'message' => $encodedMessage,
            'xtra' => '<input type="hidden" name="RelayState" value="relaystate">',
            'name' => 'SAMLResponse',
            'preventAutoSubmit' => true
        ]), 200);
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
        $idpName = null;
        $spName = null;
        if ($request->query->has('idp-name')) {
            $idpName = $request->query->get('idp-name');
        }
        if ($request->query->has('sp-name')) {
            $spName = $request->query->get('sp-name');
        }

        $attributeAggregationEnabled = (bool) $request->query->get('aa-enabled', false);
        $attributeSources = [];

        $processConsentUrl = '#';
        $fakeResponseId = '918723649';
        $fakeSp = TestEntitySeeder::buildSp($spName);
        $fakeIdP = TestEntitySeeder::buildIdP($idpName);
        $supportContact = 'Helpdesk';

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
            'urn:mace:dir:attribute-def:isMemberOf' => ['urn:collab:org:dev.openconext.local', 'urn:collab:org:example.com'],
        ];
        $attributeMotivations = [
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => 'Test  tooltip',
            'urn:mace:dir:attribute-def:givenName' => 'Test tooltip',
            'urn:mace:dir:attribute-def:isMemberOf' => 'Test tooltip',
        ];

        if ($attributeAggregationEnabled) {
            $attributes['urn:mace:surf.nl:attribute-def:eckid'] = ['joe-f12-eck-id'];
            $attributes['urn:mace:dir:attribute-def:eduPersonOrcid'] = ['https://orcid.org/0000-0002-9079-593X'];
            $nameId = new NameID();
            $nameId->setFormat(Constants::NAMEID_PERSISTENT);
            $nameId->setValue('34872398723498723497293487');

            $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'] = [$nameId];

            $attributeSources = [
                'urn:mace:dir:attribute-def:eduPersonOrcid' => 'orcid',
                'urn:mace:surf.nl:attribute-def:eckid' => 'sab',
                'urn:mace:dir:attribute-def:eduPersonTargetedID' => 'engineblock',
            ];
        }

        return new Response($this->twig->render('@theme/Authentication/View/Proxy/consent.html.twig', [
            'action' => $processConsentUrl,
            'responseId' => $fakeResponseId,
            'sp' => $fakeSp,
            'idp' => $fakeIdP,
            'idpSupport' => $supportContact,
            'attributes' => $attributes,
            'attributeSources' => $attributeSources,
            'attributeMotivations' => $attributeMotivations,
            'informationalConsent' => $fakeIdP->getConsentSettings()->isInformational($fakeSp->entityId),
            'consentCount' => 5,
            'nameId' => $this->getNameId($request),
            'nameIdIsPersistent' => $this->isPersistentNameId($request),
            'profileUrl' => $profileUrl,
            'showConsentExplanation' => $fakeIdP->getConsentSettings()->hasConsentExplanation($fakeSp->entityId),
            'consentSettings' => $fakeIdP->getConsentSettings(),
            'spEntityId' => $fakeSp->entityId,
            'hideHeader' => $this->hideHeader($request),
            'hideFooter' => $this->hideFooter($request),
        ]), 200);
    }

    private function hideHeader(Request $request): bool
    {
        return (bool) $request->query->get('hide-header', true);
    }

    private function hideFooter(Request $request): bool
    {
        return (bool) $request->query->get('hide-footer', true);
    }

    private function getNameId(Request $request): NameID
    {
        $nameIdValue = $request->query->get('name-id', 'user@openconext');
        $nameId = new NameID();
        if ($nameIdValue !== 'user@openconext') {
            $nameId->setFormat(Constants::NAMEID_UNSPECIFIED);
        }
        $nameId->setValue($nameIdValue);
        return $nameId;
    }

    private function isPersistentNameId(Request $request): bool
    {
        return (bool) $request->query->get('persistent-name-id', true);
    }
}
