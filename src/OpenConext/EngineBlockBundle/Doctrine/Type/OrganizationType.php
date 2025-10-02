<?php
namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\Organization;

class OrganizationType extends Type
{
    public const NAME = 'engineblock_organization';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 65535;
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!$value instanceof Organization) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), [Organization::class, 'null']);
        }
        return json_encode([
            'name' => $value->name,
            'displayName' => $value->displayName,
            'url' => $value->url,
        ]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Organization
    {
        if ($value === null || $value === '') {
            return null;
        }
        $decoded = json_decode($value, true);
        if (!is_array($decoded) || !isset($decoded['name'], $decoded['displayName'], $decoded['url'])) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), 'json object with name, displayName, url');
        }
        return new Organization($decoded['name'], $decoded['displayName'], $decoded['url']);
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

