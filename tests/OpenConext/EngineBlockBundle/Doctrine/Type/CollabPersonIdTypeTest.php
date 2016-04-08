<?php

namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;
use OpenConext\TestDataProvider;
use PHPUnit_Framework_TestCase as UnitTest;

class CollabPersonIdTypeTest extends UnitTest
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
        if (!Type::hasType(CollabPersonIdType::NAME)) {
            Type::addType(CollabPersonIdType::NAME, CollabPersonIdType::class);
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
        $collabPersonIdType = Type::getType(CollabPersonIdType::NAME);

        $value = $collabPersonIdType->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_collab_person_id_value_is_converted_to_the_correct_database_format()
    {
        $collabPersonIdType = Type::getType(CollabPersonIdType::NAME);
        $collabPersonId     = $this->getCollabPersonId();
        $input              = new CollabPersonId($collabPersonId);

        $output = $collabPersonIdType->convertToDatabaseValue($input, $this->platform);

        $this->assertTrue(is_string($output));
        $this->assertEquals($collabPersonId, $output);
    }

    /**
     * @test
     * @group        EngineBlockBundle
     * @group        Doctrine
     * @dataProvider invalidPhpValueProvider
     */
    public function an_invalid_value_causes_an_exception_upon_conversion_to_database_value($invalidValue)
    {
        $collabPersonIdType = Type::getType(CollabPersonIdType::NAME);

        $this->expectException(ConversionException::class);
        $collabPersonIdType->convertToDatabaseValue($invalidValue, $this->platform);
    }

    /**
     * @return array
     */
    public function invalidPhpValueProvider()
    {
        return array_merge(
            TestDataProvider::notNull(),
            [
                'no namespace' => [':openconext:homer@invalid.org'],
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
        $collabPersonIdType = Type::getType(CollabPersonIdType::NAME);

        $value = $collabPersonIdType->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function a_non_null_value_is_converted_to_a_collab_person_id()
    {
        $collabPersonIdType = Type::getType(CollabPersonIdType::NAME);
        $input        = $this->getCollabPersonId();

        $output = $collabPersonIdType->convertToPHPValue($input, $this->platform);

        $this->assertInstanceOf(CollabPersonId::class, $output);
        $this->assertEquals(new CollabPersonId($input), $output);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $collabPersonIdType = Type::getType(CollabPersonIdType::NAME);

        $this->expectException(ConversionException::class);
        $collabPersonIdType->convertToPHPValue(false, $this->platform);
    }

    /**
     * Helper method to easily generate a valid collabPersonId without having to do this in the tests.
     * Doing this in the tests would only detract from the actual test.
     *
     * @return string
     */
    private function getCollabPersonId()
    {
        $collabPersonId = CollabPersonId::generateFrom(
            new Uid('homer@invalid.org'),
            new SchacHomeOrganization('OpenConext.org')
        );

        return $collabPersonId->getCollabPersonId();
    }
}
