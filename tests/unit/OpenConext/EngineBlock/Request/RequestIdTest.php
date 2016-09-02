<?php

namespace OpenConext\EngineBlock\Request;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class RequestIdTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Request
     */
    public function request_id_is_unchanged_after_first_retrieval()
    {
        $generatedId = 'generated_id';

        $requestIdGenerator = m::mock('OpenConext\EngineBlock\Request\RequestIdGenerator');
        $requestIdGenerator->shouldReceive('generateRequestId')
            ->once()
            ->andReturn($generatedId);

        $requestId = new RequestId($requestIdGenerator);

        $this->assertEquals($generatedId, $requestId->get());
        $this->assertEquals($generatedId, $requestId->get());
    }
}
