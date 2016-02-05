<?php

namespace OpenConext\EngineBlock\Request\RequestIdGenerator;

final class UniqidGenerator implements RequestIdGenerator
{
    public function generateRequestId()
    {
        return uniqid();
    }
}
