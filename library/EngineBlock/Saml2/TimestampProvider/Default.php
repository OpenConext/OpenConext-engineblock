<?php

class EngineBlock_Saml2_TimestampProvider_Default implements EngineBlock_Saml2_TimestampProvider_Interface
{
    const TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s\Z';

    public function timestamp($deltaSeconds = 0, $time = null)
    {
        $time = (int) $time;
        if ($time === 0) {
            $time = time();
        }
        return gmdate(self::TIMESTAMP_FORMAT, $time + $deltaSeconds);
    }
}
