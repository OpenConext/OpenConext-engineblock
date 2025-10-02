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
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['array','null']);
        }
        $out = [];
        foreach ($value as $cp) {
            if (!$cp instanceof ContactPerson) {
                throw ConversionException::conversionFailedInvalidType($cp, $this->getName(), [ContactPerson::class]);
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
            throw ConversionException::conversionFailedFormat($value, $this->getName(), 'json array');
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

