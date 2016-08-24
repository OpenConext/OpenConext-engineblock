<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Metadata\Value\X509\CertificateList;
use OpenConext\Value\Saml\Metadata\Common\Endpoint;
use OpenConext\Value\Saml\Metadata\ContactPersonList;
use OpenConext\Value\Saml\Metadata\Organization;
use OpenConext\Value\Saml\NameIdFormat;
use OpenConext\Value\Saml\NameIdFormatList;
use OpenConext\Value\Serializable;

final class EntitySamlConfiguration implements Serializable
{
    /**
     * @var NameIdFormat
     */
    private $preferredNameIdFormat;

    /**
     * @var NameIdFormatList
     */
    private $allowedNameIdFormats;

    /**
     * @var CertificateList
     */
    private $certificateList;

    /**
     * @var Endpoint
     */
    private $singleLogoutService;

    /**
     * @var Endpoint
     */
    private $responseProcessingService;

    /**
     * @var ContactPersonList
     */
    private $contactPersons;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * @param NameIdFormat      $preferredNameIdFormat
     * @param NameIdFormatList  $allowedNameIdFormats
     * @param CertificateList   $certificateList
     * @param Endpoint          $singleLogoutService
     * @param Endpoint          $responseProcessingService
     * @param ContactPersonList $contactPersons
     * @param Organization|null $organization
     */
    public function __construct(
        NameIdFormat $preferredNameIdFormat,
        NameIdFormatList $allowedNameIdFormats,
        CertificateList $certificateList,
        Endpoint $singleLogoutService,
        Endpoint $responseProcessingService,
        ContactPersonList $contactPersons,
        Organization $organization = null // <- verify
    ) {
        $this->preferredNameIdFormat     = $preferredNameIdFormat;
        $this->allowedNameIdFormats      = $allowedNameIdFormats;
        $this->certificateList           = $certificateList;
        $this->singleLogoutService       = $singleLogoutService;
        $this->responseProcessingService = $responseProcessingService;
        $this->contactPersons            = $contactPersons;
        $this->organization              = $organization;
    }

    /**
     * @return NameIdFormat
     */
    public function getPreferredNameIdFormat()
    {
        return $this->preferredNameIdFormat;
    }

    /**
     * @return NameIdFormatList
     */
    public function getAllowedNameIdFormats()
    {
        return $this->allowedNameIdFormats;
    }

    /**
     * @return CertificateList
     */
    public function getCertificateList()
    {
        return $this->certificateList;
    }

    /**
     * @return Endpoint
     */
    public function getSingleLogoutService()
    {
        return $this->singleLogoutService;
    }

    /**
     * @return Endpoint
     */
    public function getResponseProcessingService()
    {
        return $this->responseProcessingService;
    }

    /**
     * @return ContactPersonList
     */
    public function getContactPersons()
    {
        return $this->contactPersons;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param EntitySamlConfiguration $other
     * @return bool
     */
    public function equals(EntitySamlConfiguration $other)
    {
        return $this->preferredNameIdFormat->equals($other->preferredNameIdFormat)
                && $this->allowedNameIdFormats->equals($other->allowedNameIdFormats)
                && $this->certificateList->equals($other->certificateList)
                && $this->singleLogoutService->equals($other->singleLogoutService)
                && $this->responseProcessingService->equals($other->responseProcessingService)
                && $this->contactPersons->equals($other->contactPersons)
                && $this->organization == $other->organization;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, [
            'preferred_name_id_format',
            'allowed_name_id_formats',
            'certificate_list',
            'single_logout_service',
            'response_processing_service',
            'contact_person_list',
            'organization'
        ]);

        return new self(
            NameIdFormat::deserialize($data['preferred_name_id_format']),
            NameIdFormatList::deserialize($data['allowed_name_id_formats']),
            CertificateList::deserialize($data['certificate_list']),
            Endpoint::deserialize($data['single_logout_service']),
            Endpoint::deserialize($data['response_processing_service']),
            ContactPersonList::deserialize($data['contact_person_list']),
            ($data['organization'] ? Organization::deserialize($data['organization']) : null)
        );
    }

    public function serialize()
    {
        return [
            'preferred_name_id_format' => $this->preferredNameIdFormat->serialize(),
            'allowed_name_id_formats' => $this->allowedNameIdFormats->serialize(),
            'certificate_list' => $this->certificateList->serialize(),
            'single_logout_service' => $this->singleLogoutService->serialize(),
            'response_processing_service' => $this->responseProcessingService->serialize(),
            'contact_person_list' => $this->contactPersons->serialize(),
            'organization' => $this->organization ? $this->organization->serialize() : null
        ];
    }

    public function __toString()
    {
        return sprintf(
            'EntitySamlConfiguration(%s, %s, %s, %s, %s, %s)',
            'PreferredNameIdFormat=' . $this->preferredNameIdFormat,
            'AllowedNameIdFormats=' . $this->allowedNameIdFormats,
            'CertificateList=' . $this->certificateList,
            'SingleLogoutService=' . $this->singleLogoutService,
            'ResponseProcessingService=' . $this->responseProcessingService,
            'ContactPersons=' . $this->contactPersons,
            'Organization=' . $this->organization
        );
    }
}
