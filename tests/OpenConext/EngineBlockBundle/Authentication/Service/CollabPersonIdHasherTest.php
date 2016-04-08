<?php

namespace OpenConext\EngineBlockBundle\Authentication\Service;

use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\TestDataProvider;
use PHPUnit_Framework_TestCase as UnitTest;

class CollabPersonIdHasherTest extends UnitTest
{
    /**
     * @test
     * @group EnginelockBundle
     * @group Authentication
     * @dataProvider invalidHashingAlgorithmProvider
     * @param mixed $invalidHashingAlgorithm
     */
    public function only_valid_hasing_algorithms_are_accepted($invalidHashingAlgorithm)
    {
        $this->expectException(InvalidArgumentException::class);

        new CollabPersonIdHasher($invalidHashingAlgorithm);
    }

    public function invalidHashingAlgorithmProvider()
    {
        return array_merge(
            TestDataProvider::notStringOrEmptyString(),
            [
                'invalid Algorithm' => ['sha132489431679431']
            ]
        );
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Authentication
     */
    public function it_generates_the_same_hash_for_the_same_input()
    {
        $collabPersonId = new CollabPersonId(CollabPersonId::URN_NAMESPACE . ':openconext:homer@domain.invalid');
        $hasher         = new CollabPersonIdHasher('sha256');

        $firstHash  = $hasher->hash($collabPersonId);
        $secondHash = $hasher->hash($collabPersonId);

        $this->assertEquals(
            $firstHash,
            $secondHash,
            'Hashing the same CollabPersonId twice should result in the same hash'
        );
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Authentication
     */
    public function different_input_leads_to_different_hashes()
    {
        $homer = new CollabPersonId(CollabPersonId::URN_NAMESPACE . ':openconext:homer@domain.invalid');
        $marge = new CollabPersonId(CollabPersonId::URN_NAMESPACE . ':openconext:marge@domain.invalid');
        $hasher         = new CollabPersonIdHasher('sha256');

        $firstHash  = $hasher->hash($homer);
        $secondHash = $hasher->hash($marge);

        $this->assertNotEquals(
            $firstHash,
            $secondHash,
            'Hashing a different CollabPersonId should result in a different hash'
        );
    }
}
