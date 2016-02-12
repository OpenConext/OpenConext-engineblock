<?php

namespace OpenConext\EngineBlock\Request;

final class UniqidGenerator implements RequestIdGenerator
{
    public function generateRequestId()
    {
        return uniqid();
    }
}
