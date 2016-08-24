<?php

namespace OpenConext\EngineBlock\Request;

interface RequestIdGenerator
{
    /**
     * Generate a randomized, single use, unique identifier
     *
     * @return string
     */
    public function generateRequestId();
}
