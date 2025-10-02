<?php
namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;

class RequestedAttributeArrayType extends Type
{
    public const NAME = 'engineblock_requested_attribute_array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 65535; return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) { return null; }
        if (!is_array($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['array','null']);
        }
        $out = [];
        foreach ($value as $attr) {
            if (!$attr instanceof RequestedAttribute) {
                throw ConversionException::conversionFailedInvalidType($attr, $this->getName(), [RequestedAttribute::class]);
            }
            $out[] = [
                'name' => $attr->name,
                'nameFormat' => $attr->nameFormat,
                'required' => $attr->required,
            ];
        }
        return json_encode($out);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null || $value === '') { return null; }
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), 'json array');
        }
        $out = [];
        foreach ($decoded as $row) {
            if (!isset($row['name'])) { continue; }
            $out[] = new RequestedAttribute($row['name'], $row['required'] ?? false, $row['nameFormat'] ?? RequestedAttribute::NAME_FORMAT_URI);
        }
        return $out;
    }

    public function getName(): string { return self::NAME; }
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool { return true; }
}

