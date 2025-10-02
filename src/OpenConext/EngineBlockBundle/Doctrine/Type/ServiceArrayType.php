<?php
namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\Service;

class ServiceArrayType extends Type
{
    public const NAME = 'engineblock_service_array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    { $column['length']=65535; return $platform->getJsonTypeDeclarationSQL($column); }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) return null;
        if (!is_array($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['array','null']);
        }
        $out=[];
        foreach ($value as $svc) {
            if (!$svc instanceof Service) {
                throw ConversionException::conversionFailedInvalidType($svc, $this->getName(), [Service::class]);
            }
            $out[]=['location'=>$svc->location,'binding'=>$svc->binding];
        }
        return json_encode($out);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null || $value==='') return null;
        $decoded=json_decode($value,true);
        if (!is_array($decoded)) {
            throw ConversionException::conversionFailedFormat($value,$this->getName(),'json array');
        }
        $out=[]; foreach($decoded as $row){ if(isset($row['location'],$row['binding'])) $out[]=new Service($row['location'],$row['binding']); }
        return $out;
    }

    public function getName(): string { return self::NAME; }
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool { return true; }
}

