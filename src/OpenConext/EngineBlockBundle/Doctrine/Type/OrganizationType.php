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
            throw new ConversionException(sprintf(
                'Invalid value for %s expected %s got %s',
                $this->getName(),
                Organization::class,
                is_object($value)? get_class($value): gettype($value)
            ));
        }
        return json_encode([
            'name' => $value->name ?? null,
            'displayName' => $value->displayName ?? null,
            'url' => $value->url ?? null,
        ]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Organization
    {
        if ($value === null || $value === '') {
            return null;
        }
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            throw new ConversionException(sprintf(
                'Invalid format for %s expected json object with name, displayName, url got: %s',
                $this->getName(),
                substr((string)$value,0,120)
            ));
        }
        // Accept keys even if null; if keys missing entirely then error
        foreach (['name','displayName','url'] as $k) {
            if (!array_key_exists($k, $decoded)) {
                throw new ConversionException(sprintf(
                    'Invalid format for %s missing key %s in: %s',
                    $this->getName(),
                    $k,
                    substr((string)$value,0,120)
                ));
            }
        }
        // If all three are null treat as empty organization (legacy)
        if ($decoded['name'] === null && $decoded['displayName'] === null && $decoded['url'] === null) {
            return new Organization('', '', '');
        }
        return new Organization($decoded['name'] ?? '', $decoded['displayName'] ?? '', $decoded['url'] ?? '');
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
