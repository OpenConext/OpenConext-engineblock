<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class UidTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     *
     * @param mixed $notStringOrEmptyString
     */
    public function uid_must_be_a_non_empty_string($notStringOrEmptyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new Uid($notStringOrEmptyString);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function uid_can_be_retrieved()
    {
        $uidValue = md5('foobar');

        $uid = new Uid($uidValue);

        $this->assertSame($uidValue, $uid->getUid());
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function uid_equality_is_determined_based_on_value()
    {
        $base = new Uid('some:uid');
        $same = new Uid('some:uid');
        $different = new Uid('a:different:uid');

        $this->assertTrue($base->equals($same));
        $this->assertFalse($base->equals($different));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Authentication
     */
    public function a_uid_can_be_cast_to_string()
    {
        $uid = new Uid('some:uid');

        $this->assertInternalType('string', (string) $uid);
    }
}
