<?php
namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\ShibMdScope;

class ShibMdScopeArrayType extends Type
{
    public const NAME = 'engineblock_shib_md_scope_array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    { $column['length']=65535; return $platform->getJsonTypeDeclarationSQL($column); }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) return null;
        if (!is_array($value)) {
            throw ConversionException::conversionFailedInvalidType($value,$this->getName(),['array','null']);
        }
        $out=[]; foreach($value as $scope){ if(!$scope instanceof ShibMdScope){ throw ConversionException::conversionFailedInvalidType($scope,$this->getName(),[ShibMdScope::class]); } $out[]=['allowed'=>$scope->allowed,'regexp'=>$scope->regexp]; }
        return json_encode($out);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null || $value==='') return null;
        $decoded=json_decode($value,true);
        if(!is_array($decoded)) { throw ConversionException::conversionFailedFormat($value,$this->getName(),'json array'); }
        $out=[]; foreach($decoded as $row){ $scope=new ShibMdScope(); $scope->allowed=$row['allowed']??''; $scope->regexp=$row['regexp']??''; $out[]=$scope; }
        return $out;
    }

    public function getName(): string { return self::NAME; }
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool { return true; }
}

