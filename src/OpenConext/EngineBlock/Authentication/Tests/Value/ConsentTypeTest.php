<?php

namespace OpenConext\EngineBlock\Authentication\Tests\Value;

use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use PHPUnit_Framework_TestCase as TestCase;

class ConsentTypeTest extends TestCase
{
    /**
     * @test
     * @group Authentication
     * @group Consent
     * @group Value
     *
     * @dataProvider invalidConsentTypeProvider
     * @expectedException \OpenConext\EngineBlock\Authentication\Exception\InvalidArgumentException
     */
    public function cannot_be_other_than_implicit_or_explicit($invalid)
    {
        $invalidConsentType = new ConsentType($invalid);
    }

    /**
     * @test
     * @group Authentication
     * @group Consent
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
     * @group Authentication
     * @group Consent
     * @group Value
     */
    public function same_type_of_consent_types_are_equal()
    {
        $explicit = ConsentType::explicit();
        $implicit = ConsentType::implicit();

        $this->assertTrue($explicit->equals(ConsentType::explicit()));
        $this->assertTrue($implicit->equals(ConsentType::implicit()));
    }

    public function invalidConsentTypeProvider()
    {
        return array(
            'invalid string'    => array('invalid'),
            'empty string'      => array(''),
            'integer'           => array(1),
            'null'              => array(null),
            'array'             => array(array()),
            'boolean'           => array(true)
        );
    }
}
