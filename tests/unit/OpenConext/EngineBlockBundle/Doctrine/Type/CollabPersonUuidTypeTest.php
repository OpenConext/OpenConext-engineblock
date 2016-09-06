<?php

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Metadata\Value\SamlEntityUuid;
use OpenConext\TestDataProvider;
use PHPUnit_Framework_TestCase as UnitTest;
use Ramsey\Uuid\Uuid;

class CollabPersonUuidTypeTest extends UnitTest
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
        if (!Type::hasType(CollabPersonUuidType::NAME)) {
            Type::addType(CollabPersonUuidType::NAME, CollabPersonUuidType::class);
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
        $collabPersonUuidType = Type::getType(CollabPersonUuidType::NAME);

        $value = $collabPersonUuidType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_collab_person_uuid_value_is_converted_to_the_correct_format()
    {
        $collabPersonUuidType = Type::getType(CollabPersonUuidType::NAME);
        $input                = CollabPersonUuid::generate();
        $uuid                 = $input->getUuid();

        $output = $collabPersonUuidType->convertToDatabaseValue($input, $this->platform);

        $this->assertTrue(is_string($output));
        $this->assertEquals($uuid, $output);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     * @dataProvider invalidPhpValueProvider
     */
    public function an_invalid_value_causes_an_exception_upon_conversion_to_database_value($invalidValue)
    {
        $collabPersonUuidType = Type::getType(CollabPersonUuidType::NAME);

        $this->expectException(ConversionException::class);
        $collabPersonUuidType->convertToDatabaseValue($invalidValue, $this->platform);
    }

    /**
     * @return array
     */
    public function invalidPhpValueProvider()
    {
        return array_merge(
            TestDataProvider::notNull(),
            [
                'uuid object'          => [Uuid::uuid4()],
                'similar value object' => [SamlEntityUuid::fromString((string) Uuid::uuid4())]
            ]
        );
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_null_value_remains_null_when_converting_from_db_to_php_value()
    {
        $collabPersonUuidType = Type::getType(CollabPersonUuidType::NAME);

        $value = $collabPersonUuidType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_non_null_value_is_converted_to_a_collab_person_uuid()
    {
        $collabPersonUuidType = Type::getType(CollabPersonUuidType::NAME);
        $uuid                 = CollabPersonUuid::generate()->getUuid();

        $output = $collabPersonUuidType->convertToPHPValue($uuid, $this->platform);

        $this->assertInstanceOf(CollabPersonUuid::class, $output);
        $this->assertTrue((new CollabPersonUuid($uuid))->equals($output));
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $collabPersonUuidType = Type::getType(CollabPersonUuidType::NAME);

        $this->expectException(ConversionException::class);
        $collabPersonUuidType->convertToPHPValue(false, $this->platform);
    }
}
