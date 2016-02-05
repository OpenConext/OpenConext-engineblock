<?php

namespace OpenConext\EngineBlock\Authentication\Tests\Value;

use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use PHPUnit_Framework_TestCase as TestCase;

class ConsentTypeTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @group Value
     *
     * @dataProvider      \OpenConext\TestDataProvider::notStringOrEmptyString
     * @expectedException \OpenConext\EngineBlock\Exception\InvalidArgumentException
     *
     * @param mixed $invalid
     */
    public function cannot_be_other_than_implicit_or_explicit($invalid)
    {
        $invalidConsentType = new ConsentType($invalid);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @group Value
     */
    public function different_consent_types_are_not_equal()
    {
        $explicit = ConsentType::explicit();
        $implicit = ConsentType::implicit();

        $this->assertFalse($explicit->equals($implicit));
        $this->assertFalse($implicit->equals($explicit));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @group Value
     */
    public function same_type_of_consent_types_are_equal()
    {
        $explicit = ConsentType::explicit();
        $implicit = ConsentType::implicit();

        $this->assertTrue($explicit->equals(ConsentType::explicit()));
        $this->assertTrue($implicit->equals(ConsentType::implicit()));
    }
}
