<?php
namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;

class CertificateArrayType extends Type
{
    public const NAME = 'engineblock_certificate_array';

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
        if (!is_array($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['array','null']);
        }
        $out = [];
        foreach ($value as $cert) {
            if (!$cert instanceof X509CertificateLazyProxy) {
                throw ConversionException::conversionFailedInvalidType($cert, $this->getName(), [X509CertificateLazyProxy::class]);
            }
            // There is a toCertData method in lazy proxy returning original data
            $ref = new \ReflectionClass($cert);
            $prop = $ref->getProperty('certData');
            $prop->setAccessible(true);
            $out[] = $prop->getValue($cert);
        }
        return json_encode($out);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), 'json array');
        }
        $factory = new X509CertificateFactory();
        $certs = [];
        foreach ($decoded as $certData) {
            $certs[] = new X509CertificateLazyProxy($factory, $certData);
        }
        return $certs;
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

