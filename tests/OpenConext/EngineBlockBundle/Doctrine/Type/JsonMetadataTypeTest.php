<?php

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as UnitTest;

class JsonMetadataTypeTest extends UnitTest
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
        if (!Type::hasType(JsonMetadataType::NAME)) {
            Type::addType(JsonMetadataType::NAME, 'OpenConext\EngineBlockBundle\Doctrine\Type\JsonMetadataType');
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
        $entityIdType = Type::getType(JsonMetadataType::NAME);

        $value = $entityIdType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_non_null_value_is_converted_to_the_correct_format()
    {
        $entity           = new Entity(new EntityId('OpenConext.org'), EntityType::SP());
        $jsonMetadataType = Type::getType(JsonMetadataType::NAME);
        $metadata         = $entity->serialize();

        $output = $jsonMetadataType->convertToDatabaseValue($metadata, $this->platform);

        $this->assertTrue(is_string($output));
        $this->assertEquals(json_encode($metadata), $output);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_null_value_remains_null_when_converting_from_db_to_php_value()
    {
        $jsonMetadataType = Type::getType(JsonMetadataType::NAME);

        $value = $jsonMetadataType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group engineblockbundle
     * @group doctrine
     */
    public function a_non_null_value_is_converted_to_an_array()
    {
        $jsonMetadataType = Type::getType(JsonMetadataType::NAME);
        $entity           = new Entity(new EntityId('https://www.openconext.org/auth'), EntityType::SP());
        $metadata         = json_encode($entity->serialize());

        $output = $jsonMetadataType->convertToPHPValue($metadata, $this->platform);

        $this->assertInternalType('array', $output);
        $this->assertEquals($entity->serialize(), $output);
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
        $entityIdType = Type::getType(EntityIdType::NAME);

        $entityIdType->convertToPHPValue(false, $this->platform);
    }
}
