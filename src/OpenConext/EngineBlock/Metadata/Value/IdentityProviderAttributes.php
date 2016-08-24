<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class IdentityProviderAttributes implements Serializable
{
    /**
     * @var EntityAttributes
     */
    private $entityAttributes;

    /**
     * @var bool
     */
    private $isHidden;

    /**
     * @var bool
     */
    private $enabledInWayf;

    /**
     * @var Keywords
     */
    private $keywords;

    /**
     * @param EntityAttributes $entityAttributes
     * @param bool             $isHidden
     * @param bool             $enabledInWayf
     * @param Keywords         $keywords
     */
    public function __construct(EntityAttributes $entityAttributes, $isHidden, $enabledInWayf, Keywords $keywords)
    {
        Assertion::boolean($isHidden);
        Assertion::boolean($enabledInWayf);

        $this->entityAttributes = $entityAttributes;
        $this->isHidden         = $isHidden;
        $this->enabledInWayf    = $enabledInWayf;
        $this->keywords         = $keywords;
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
     * @return bool
     */
    public function isHidden()
    {
        return $this->isHidden;
    }

    /**
     * @return bool
     */
    public function isEnabledInWay()
    {
        return $this->enabledInWayf;
    }

    /**
     * @return Keywords
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param $locale
     * @return null|LocalizedKeywords
     */
    public function getKeywordsForLocale($locale)
    {
        if (!$this->keywords->hasKeywordsForLocale($locale)) {
            return null;
        }

        return $this->keywords->getKeywordsForLocale($locale);
    }

    /**
     * @param IdentityProviderAttributes $other
     * @return bool
     */
    public function equals(IdentityProviderAttributes $other)
    {
        return $this->entityAttributes->equals($other->entityAttributes)
                && $this->isHidden === $other->isHidden
                && $this->enabledInWayf === $other->enabledInWayf
                && $this->keywords->equals($other->keywords);
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, ['entity_attributes', 'hidden', 'enabled_in_wayf', 'keywords']);

        return new self(
            EntityAttributes::deserialize($data['entity_attributes']),
            $data['hidden'],
            $data['enabled_in_wayf'],
            Keywords::deserialize($data['keywords'])
        );
    }

    public function serialize()
    {
        return [
            'entity_attributes' => $this->entityAttributes->serialize(),
            'hidden'            => $this->isHidden,
            'enabled_in_wayf'   => $this->enabledInWayf,
            'keywords'          => $this->keywords->serialize()
        ];
    }

    public function __toString()
    {
        return sprintf(
            'IdentityProviderAttributes(%s, hidden=%s, enabledInWayf=%s, keywords=%s)',
            $this->entityAttributes,
            ($this->isHidden ? 'true' : 'false'),
            ($this->enabledInWayf ? 'true' : 'false'),
            $this->keywords
        );
    }
}
