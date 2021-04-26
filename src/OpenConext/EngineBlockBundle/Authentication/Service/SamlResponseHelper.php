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

namespace OpenConext\EngineBlockBundle\Authentication\Service;

use DateTime;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\Exception\RuntimeException;
use SAML2\Constants;
use SAML2\Response as SAMLResponse;
use SAML2\XML\saml\Issuer;
use function sprintf;

class SamlResponseHelper
{
    /**
     * @var MetadataRepositoryInterface
     */
    private $metaDataRepository;

    public function __construct(MetadataRepositoryInterface $metaDataRepository)
    {
        $this->metaDataRepository = $metaDataRepository;
    }

    public function createAuthnFailedResponse(string $spEntityId, string $idpEntityId, string $originalRequestId, string $message): string
    {
        $issuer = new Issuer();
        $issuer->setValue($idpEntityId);
        $response = new SAMLResponse();
        $response->setDestination($this->getAcu($spEntityId));
        $response->setIssuer($issuer);
        $response->setIssueInstant((new DateTime('now'))->getTimestamp());
        $response->setInResponseTo($originalRequestId);
        $response->setStatus([
            'Code' => Constants::STATUS_RESPONDER,
            'SubCode' => Constants::STATUS_AUTHN_FAILED,
            'Message' => $message
        ]);
        return base64_encode($response->toUnsignedXML()->ownerDocument->saveXML());
    }

    public function getAcu(string $spEntityId)
    {
        $sp = $this->metaDataRepository->findServiceProviderByEntityId($spEntityId);
        if ($sp) {
            $acsLocations = $sp->assertionConsumerServices;
            foreach ($acsLocations as $acsLocation) {
                if ($acsLocation->binding === Constants::BINDING_HTTP_POST) {
                    return $acsLocation->location;
                }
            }
            throw new RuntimeException('No suitable ACS location could be find, no HTTP-POST binding available');
        }
        throw new RuntimeException(
            sprintf('The SP with entity id "%s" could not be found while building the error response', $spEntityId)
        );
    }
}
