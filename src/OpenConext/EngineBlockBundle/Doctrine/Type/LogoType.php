<?php

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\Logo;

class LogoType extends Type
{
    public const NAME = 'engineblock_logo';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 65535; // prefer TEXT over LONGTEXT
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /**
     * @param Logo|null $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!$value instanceof Logo) {
            throw new ConversionException(sprintf(
                'Invalid value for %s expected %s got %s',
                $this->getName(),
                Logo::class,
                is_object($value)? get_class($value): gettype($value)
            ));
        }
        return json_encode([
            'url' => $value->url,
            'width' => $value->width,
            'height' => $value->height,
        ]);
    }

    /**
     * @return Logo|null
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Logo
    {
        if ($value === null || $value === '') {
            return null;
        }
        $decoded = json_decode($value, true);
        if (!is_array($decoded) || !array_key_exists('url', $decoded)) {
            throw new ConversionException(sprintf(
                'Invalid format for %s expected json object with url, got: %s',
                $this->getName(),
                substr((string)$value,0,120)
            ));
        }
        $logo = new Logo($decoded['url']);
        $logo->width = $decoded['width'] ?? null;
        $logo->height = $decoded['height'] ?? null;
        return $logo;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
