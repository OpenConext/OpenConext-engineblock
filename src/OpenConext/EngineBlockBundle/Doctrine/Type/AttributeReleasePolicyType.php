
<?php
namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use InvalidArgumentException;
use TypeError;

class AttributeReleasePolicyType extends Type
{
    public const NAME = 'engineblock_attribute_release_policy';

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
        if (!$value instanceof AttributeReleasePolicy) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), [AttributeReleasePolicy::class, 'null']);
        }
        return json_encode($value->getAttributeRules());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?AttributeReleasePolicy
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                throw new InvalidArgumentException('Decoded ARP is not array');
            }
            return new AttributeReleasePolicy($decoded);
        } catch (InvalidArgumentException | TypeError $e) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), AttributeReleasePolicy::class);
        }
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

