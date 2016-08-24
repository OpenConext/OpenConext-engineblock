<?php

namespace OpenConext\EngineBlock\Request;

final class RequestId
{
    /**
     * @var RequestIdGenerator
     */
    private $generator;

    /**
     * @var string
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
     * @return string
     */
    public function get()
    {
        if ($this->requestId === null) {
            $this->requestId = $this->generator->generateRequestId();
        }

        return $this->requestId;
    }
}
