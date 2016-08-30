<?php

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\Value\Saml\EntityId;
use PHPUnit_Framework_TestCase as UnitTest;

class EntityIdTypeTest extends UnitTest
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
        if (!Type::hasType(EntityIdType::NAME)) {
            Type::addType(EntityIdType::NAME, 'OpenConext\EngineBlockBundle\Doctrine\Type\EntityIdType');
        }
    }

    public function setUp()
    {
        $this->platform = new MySqlPlatform();
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_remains_null_in_to_sql_conversion()
    {
        $entityIdType = Type::getType(EntityIdType::NAME);

        $value = $entityIdType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_non_null_value_is_converted_to_the_correct_format()
    {
        $entityIdType = Type::getType(EntityIdType::NAME);
        $entity       = 'OpenConext.org';
        $input        = new EntityId($entity);

        $output = $entityIdType->convertToDatabaseValue($input, $this->platform);

        $this->assertTrue(is_string($output));
        $this->assertEquals($entity, $output);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_remains_null_when_converting_from_db_to_php_value()
    {
        $entityIdType = Type::getType(EntityIdType::NAME);

        $value = $entityIdType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_non_null_value_is_converted_to_an_entity_id()
    {
        $entityIdType = Type::getType(EntityIdType::NAME);
        $input        = 'OpenConext.org';

        $output = $entityIdType->convertToPHPValue($input, $this->platform);

        $this->assertInstanceOf('OpenConext\Value\Saml\EntityId', $output);
        $this->assertEquals(new EntityId($input), $output);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $entityIdType = Type::getType(EntityIdType::NAME);

        $this->expectException(ConversionException::class);
        $entityIdType->convertToPHPValue(false, $this->platform);
    }
}
