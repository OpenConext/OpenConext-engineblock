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
use EngineBlock_Saml2_ResponseAnnotationDecorator;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\Exception\RuntimeException;
use SAML2\Constants;
use SAML2\Response as SAMLResponse;
use SAML2\XML\saml\Issuer;

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

    public function createAuthnFailedResponse(
        string $spEntityId,
        string $idpEntityId,
        string $originalRequestId,
        string $message,
        EngineBlock_Saml2_ResponseAnnotationDecorator $originalResponse
    ): string {
        $response = new SAMLResponse();
        $response->setDestination($this->getAcu($spEntityId));
        $response->setIssuer($originalResponse->getIssuer());
        $response->setIssueInstant(time());
        $response->setInResponseTo($originalRequestId);
        $status = $originalResponse->getStatus();
        $response->setStatus([
            'Code' => array_key_exists('Code', $status) ? $status['Code'] : Constants::STATUS_RESPONDER,
            'SubCode' => array_key_exists('SubCode', $status) ? $status['SubCode'] : Constants::STATUS_AUTHN_FAILED,
            'Message' => $message
        ]);

        // Copy of behavior found in: https://github.com/OpenConext/OpenConext-engineblock/blob/8aea9cdaa8162d92b391c35c7c66ce6802273f72/library/EngineBlock/Corto/ProxyServer.php#L582-L598
        $serviceProvider = $this->metaDataRepository->findServiceProviderByEntityId($spEntityId);
        $isTransparant = $serviceProvider->getCoins()->isTransparentIssuer();
        if ($isTransparant) {
            $issuer = new Issuer();
            $issuer->setValue($originalResponse->getOriginalIssuer());
            $response->setIssuer($issuer);
        }

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
