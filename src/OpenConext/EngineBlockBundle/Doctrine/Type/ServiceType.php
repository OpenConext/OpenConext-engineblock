<?php
namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\Service;

class ServiceType extends Type
{
    public const NAME = 'engineblock_service';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 65535; return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) { return null; }
        if (!$value instanceof Service) {
            throw new ConversionException(sprintf('Invalid value for %s expected %s got %s', $this->getName(), Service::class, is_object($value)? get_class($value): gettype($value)));
        }
        return json_encode(['location' => $value->location, 'binding' => $value->binding]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Service
    {
        if ($value === null || $value === '') { return null; }
        $decoded = json_decode($value, true);
        if (!is_array($decoded) || !isset($decoded['location'], $decoded['binding'])) {
            throw new ConversionException(sprintf('Invalid format for %s expected json object with location,binding got: %s', $this->getName(), substr((string)$value,0,120)));
        }
        return new Service($decoded['location'], $decoded['binding']);
    }

    public function getName(): string { return self::NAME; }
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool { return true; }
}
