<?php

namespace OpenConext\EngineBlock\Request;

final class RequestId
{
    /**
     * @var RequestIdGenerator
     */
    private $generator;

    /**
     * @var mixed
     */
    private $requestId;

    /**
     * @param RequestIdGenerator $generator
     */
    public function __construct(RequestIdGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        if ($this->requestId === null) {
            $this->requestId = $this->generator->generateRequestId();
        }
        return $this->requestId;
    }
}
