<?php
namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\ContactPerson;

class ContactPersonArrayType extends Type
{
    public const NAME = 'engineblock_contact_person_array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = 65535;
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) { return null; }
        if (!is_array($value)) {
            throw new ConversionException(sprintf('Invalid value for %s expected array got %s', $this->getName(), gettype($value)));
        }
        $out = [];
        foreach ($value as $cp) {
            if (!$cp instanceof ContactPerson) {
                throw new ConversionException(sprintf('Invalid element for %s expected %s got %s', $this->getName(), ContactPerson::class, is_object($cp)? get_class($cp): gettype($cp)));
            }
            $out[] = [
                'contactType' => $cp->contactType,
                'emailAddress' => $cp->emailAddress,
                'telephoneNumber' => $cp->telephoneNumber,
                'givenName' => $cp->givenName,
                'surName' => $cp->surName,
            ];
        }
        return json_encode($out);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null || $value === '') { return null; }
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            if (is_string($value) && preg_match('/^[aOs]:/i', $value)) {
                $legacy = @unserialize($value, ['allowed_classes' => true]);
                if (is_array($legacy)) {
                    $out = [];
                    foreach ($legacy as $cp) {
                        if ($cp instanceof ContactPerson) { $out[] = $cp; continue; }
                        if (is_array($cp) && isset($cp['contactType'])) {
                            $obj = new ContactPerson($cp['contactType']);
                            $obj->emailAddress = $cp['emailAddress'] ?? '';
                            $obj->telephoneNumber = $cp['telephoneNumber'] ?? '';
                            $obj->givenName = $cp['givenName'] ?? '';
                            $obj->surName = $cp['surName'] ?? '';
                            $out[] = $obj;
                        }
                    }
                    return $out;
                }
            }
            throw new ConversionException(sprintf('Invalid format for %s expected json array got: %s', $this->getName(), substr((string)$value,0,120)));
        }
        $out = [];
        foreach ($decoded as $row) {
            if (!isset($row['contactType'])) { continue; }
            $cp = new ContactPerson($row['contactType']);
            $cp->emailAddress = $row['emailAddress'] ?? '';
            $cp->telephoneNumber = $row['telephoneNumber'] ?? '';
            $cp->givenName = $row['givenName'] ?? '';
            $cp->surName = $row['surName'] ?? '';
            $out[] = $cp;
        }
        return $out;
    }

    public function getName(): string { return self::NAME; }
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool { return true; }
}
