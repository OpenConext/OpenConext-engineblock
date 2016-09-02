<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class SchacHomeOrganizationTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function schac_home_organization_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new SchacHomeOrganization($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function schac_home_organization_can_be_retrieved()
    {
        $schacHomeOrganizationValue = 'OpenConext.org';

        $schacHomeOrganization = new SchacHomeOrganization($schacHomeOrganizationValue);

        $this->assertSame($schacHomeOrganizationValue, $schacHomeOrganization->getSchacHomeOrganization());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function schac_home_organization_equality_is_determined_based_on_value()
    {
        $base      = new SchacHomeOrganization('OpenConext.org');
        $same      = new SchacHomeOrganization('OpenConext.org');
        $different = new SchacHomeOrganization('BabelFish Inc.');

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function a_schac_home_organization_can_be_cast_to_string()
    {
        $schacHomeOrganization = new SchacHomeOrganization('OpenConext.org');

        $this->assertInternalType('string', (string) $schacHomeOrganization);
    }
}
