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
            throw new ConversionException(sprintf('Invalid value for %s expected array got %s', $this->getName(), gettype($value)));
        }
        $out = [];
        foreach ($value as $cert) {
            if (!$cert instanceof X509CertificateLazyProxy) {
                throw new ConversionException(sprintf('Invalid certificate element for %s expected %s got %s', $this->getName(), X509CertificateLazyProxy::class, is_object($cert)? get_class($cert): gettype($cert)));
            }
            if (method_exists($cert, 'toCertData')) {
                $out[] = $cert->toCertData();
            }
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
            // Legacy fallback: serialized PHP array
            if (is_string($value) && preg_match('/^[aOs]:/i', $value)) {
                $legacy = @unserialize($value, ['allowed_classes' => true]);
                if (is_array($legacy)) {
                    // Assume elements expose certData or toCertData
                    $factory = new X509CertificateFactory();
                    $certs = [];
                    foreach ($legacy as $legacyCert) {
                        if (is_object($legacyCert) && method_exists($legacyCert, 'toCertData')) {
                            $certs[] = new X509CertificateLazyProxy($factory, $legacyCert->toCertData());
                        } elseif (is_string($legacyCert)) {
                            $certs[] = new X509CertificateLazyProxy($factory, $legacyCert);
                        }
                    }
                    return $certs;
                }
            }
            throw new ConversionException(sprintf('Invalid format for %s expected json array got: %s', $this->getName(), substr((string)$value,0,120)));
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
