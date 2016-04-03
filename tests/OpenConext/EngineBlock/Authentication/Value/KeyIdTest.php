<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class KeyIdTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function key_id_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new KeyId($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function key_id_can_be_retrieved()
    {
        $keyIdValue = '20160403';

        $keyId = new KeyId($keyIdValue);

        $this->assertEquals($keyIdValue, $keyId->getKeyId());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function key_ids_are_only_equal_if_created_with_the_same_value()
    {
        $firstId  = '20160403';
        $secondId = 'default';

        $base      = new KeyId($firstId);
        $same      = new KeyId($firstId);
        $different = new KeyId($secondId);

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function a_key_id_can_be_cast_to_string()
    {
        $keyId = new KeyId('20160403');

        $this->assertInternalType('string', (string) $keyId);
    }
}
