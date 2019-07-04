<?php

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\Coins;

class MetadataCoinType extends Type
{
    const NAME = 'engineblock_metadata_coins';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        if (!$value instanceof Coins) {
            throw new ConversionException(
                sprintf(
                    'Value "%s" must be null or an instance of Coins to be able to ' .
                    'convert it to a database value',
                    is_object($value) ? get_class($value) : (string)$value
                )
            );
        }

        return $value->toJson();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }

        try {
            $coins = Coins::fromJson($value);
        } catch (InvalidArgumentException $e) {
            // get nice standard message, so we can throw it keeping the exception chain
            $doctrineExceptionMessage = ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                'valid serialized coin json'
            )->getMessage();

            throw new ConversionException($doctrineExceptionMessage, 0, $e);
        }

        return $coins;
    }

    public function getName()
    {
        return self::NAME;
    }
}
