<?php

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class JsonMetadataType extends Type
{
    const NAME = 'engineblock_json_metadata';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // ensure that we get a LONGTEXT field if mysql, which is the default if no lenght is given
        if (isset($fieldDeclaration['length'])) {
            unset($fieldDeclaration['length']);
        }

        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        return json_encode($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        $metadata = json_decode($value, true);

        if (json_last_error()) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                sprintf('json deserializable string. Error: "%s"', json_last_error_msg())
            );
        }

        return $metadata;
    }

    public function getName()
    {
        return self::NAME;
    }
}
