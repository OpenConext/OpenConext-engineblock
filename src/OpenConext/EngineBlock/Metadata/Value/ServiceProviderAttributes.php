<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Saml\Metadata\Common\LocalizedUri;
use OpenConext\Value\Serializable;

final class ServiceProviderAttributes implements Serializable
{
    /**
     * @var EntityAttributes
     */
    private $entityAttributes;

    /**
     * @var LocalizedUri
     */
    private $termsOfServiceUrl;

    /**
     * @var LocalizedSupportUrl
     */
    private $localizedSupportUrl;

    public function __construct(
        EntityAttributes $entityAttributes,
        Url $termsOfServiceUrl,
        LocalizedSupportUrl $localizedSupportUrl
    ) {
        $this->entityAttributes    = $entityAttributes;
        $this->termsOfServiceUrl   = $termsOfServiceUrl;
        $this->localizedSupportUrl = $localizedSupportUrl;
    }

    /**
     * @return LocalizedServiceName
     */
    public function getServiceName()
    {
        return $this->entityAttributes->getServiceName();
    }

    /**
     * @return LocalizedDescription
     */
    public function getDescription()
    {
        return $this->entityAttributes->getDescription();
    }

    /**
     * @return Logo
     */
    public function getLogo()
    {
        return $this->entityAttributes->getLogo();
    }

    /**
     * @return LocalizedUri
     */
    public function getTermsOfServiceUrl()
    {
        return $this->termsOfServiceUrl;
    }

    /**
     * @return LocalizedSupportUrl
     */
    public function getSupportUrl()
    {
        return $this->localizedSupportUrl;
    }

    /**
     * @param ServiceProviderAttributes $other
     * @return bool
     */
    public function equals(ServiceProviderAttributes $other)
    {
        return $this->entityAttributes->equals($other->entityAttributes)
                && $this->termsOfServiceUrl->equals($other->termsOfServiceUrl)
                && $this->localizedSupportUrl->equals($other->localizedSupportUrl);
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, ['entity_attributes', 'terms_of_service_url', 'support_urls']);

        return new self(
            EntityAttributes::deserialize($data['entity_attributes']),
            Url::deserialize($data['terms_of_service_url']),
            LocalizedSupportUrl::deserialize($data['support_urls'])
        );
    }

    public function serialize()
    {
        return [
            'entity_attributes' => $this->entityAttributes->serialize(),
            'terms_of_service_url' => $this->termsOfServiceUrl->serialize(),
            'support_urls' => $this->localizedSupportUrl->serialize()
        ];
    }

    public function __toString()
    {
        return sprintf(
            'ServiceProviderAttributes(%s, TermsOfServiceUrl=%s, SupportUrls=%s)',
            $this->entityAttributes,
            $this->termsOfServiceUrl,
            $this->localizedSupportUrl
        );
    }
}
