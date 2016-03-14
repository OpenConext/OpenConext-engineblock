<?php

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Metadata\Value\SamlEntityUuid;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as UnitTest;

class SamlEntityUuidTypeTest extends UnitTest
{
    /**
     * @var MySqlPlatform
     */
    private $platform;

    /**
     * Register the type, since we're forced to use the factory method.
     */
    public static function setUpBeforeClass()
    {
        if (!Type::hasType(SamlEntityUuidType::NAME)) {
            Type::addType(SamlEntityUuidType::NAME, 'OpenConext\EngineBlockBundle\Doctrine\Type\SamlEntityUuidType');
        }
    }

    public function setUp()
    {
        $this->platform = new MySqlPlatform();
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_null_value_remains_null_in_to_sql_conversion()
    {
        $samlEntityUuidType = Type::getType(SamlEntityUuidType::NAME);

        $value = $samlEntityUuidType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_non_null_value_is_converted_to_the_correct_format()
    {
        $entity             = new Entity(new EntityId('OpenConext.org'), EntityType::SP());
        $samlEntityUuidType = Type::getType(SamlEntityUuidType::NAME);
        $input              = SamlEntityUuid::forEntity($entity);
        $uuid               = $input->getUuid();

        $output = $samlEntityUuidType->convertToDatabaseValue($input, $this->platform);

        $this->assertTrue(is_string($output));
        $this->assertEquals($uuid, $output);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_null_value_remains_null_when_converting_from_db_to_php_value()
    {
        $samlEntityUuidType = Type::getType(SamlEntityUuidType::NAME);

        $value = $samlEntityUuidType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_non_null_value_is_converted_to_an_saml_entity_uuid()
    {
        $samlEntityUuidType = Type::getType(SamlEntityUuidType::NAME);
        $uuid               = '70f08ac9-773d-463c-820a-e439651fd282';

        $output = $samlEntityUuidType->convertToPHPValue($uuid, $this->platform);

        $this->assertInstanceOf('OpenConext\EngineBlock\Metadata\Value\SamlEntityUuid', $output);
        $this->assertEquals(SamlEntityUuid::fromString($uuid), $output);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     *
     * @expectedException \Doctrine\DBAL\Types\ConversionException
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $entityIdType = Type::getType(SamlEntityUuidType::NAME);

        $entityIdType->convertToPHPValue(false, $this->platform);
    }
}
