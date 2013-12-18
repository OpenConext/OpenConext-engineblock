<?php

class EngineBlock_Saml2_TimestampProvider_Fixture implements EngineBlock_Saml2_TimestampProvider_Interface
{
    const FIXTURE_FILE = '/tmp/eb-fixtures/saml2/time';

    public function timestamp($deltaSeconds = 0, $time = null)
    {
        if (file_exists(self::FIXTURE_FILE)) {
            $time =  (int)trim(file_get_contents(self::FIXTURE_FILE));
        }

        $defaultTimestampProvider = new EngineBlock_Saml2_TimestampProvider_Default();
        return $defaultTimestampProvider->timestamp($deltaSeconds, $time);
    }
}
