<?php

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as UnitTest;

class EntityTypeTypeTest extends UnitTest
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
        if (!Type::hasType(EntityTypeType::NAME)) {
            Type::addType(EntityTypeType::NAME, 'OpenConext\EngineBlockBundle\Doctrine\Type\EntityTypeType');
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
        $entityType = Type::getType(EntityTypeType::NAME);

        $value = $entityType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_non_null_value_is_converted_to_the_correct_format()
    {
        $entityTypeType = Type::getType(EntityTypeType::NAME);
        $input          = EntityType::IdP();

        $output = $entityTypeType->convertToDatabaseValue($input, $this->platform);

        $this->assertTrue(is_string($output));
        $this->assertEquals(EntityType::TYPE_IDP, $output);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_null_value_remains_null_when_converting_from_db_to_php_value()
    {
        $entityTypeType = Type::getType(EntityTypeType::NAME);

        $value = $entityTypeType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_non_null_value_is_converted_to_an_entity_type()
    {
        $entityTypeType = Type::getType(EntityTypeType::NAME);
        $input          = EntityType::TYPE_SP;

        $output = $entityTypeType->convertToPHPValue($input, $this->platform);

        $this->assertInstanceOf('OpenConext\Value\Saml\EntityType', $output);
        $this->assertEquals(new EntityType($input), $output);
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
        $entityTypeType = Type::getType(EntityTypeType::NAME);
        $entityTypeType->convertToPHPValue(false, $this->platform);
    }
}
