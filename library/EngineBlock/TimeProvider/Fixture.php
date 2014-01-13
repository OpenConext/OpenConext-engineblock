<?php

class EngineBlock_TimeProvider_Fixture implements EngineBlock_TimeProvider_Interface
{
    const FIXTURE_FILE = '/tmp/eb-fixtures/saml2/time';

    static $s_time;

    public function timestamp($deltaSeconds = 0, $time = null)
    {
        $time = $this->time();

        $defaultTimeProvider = new EngineBlock_TimeProvider_Default();
        return $defaultTimeProvider->timestamp($deltaSeconds, $time);
    }

    public function time()
    {
        if (isset(self::$s_time)) {
            return self::$s_time;
        }

        if (!file_exists(self::FIXTURE_FILE)) {
            return time();
        }

        self::$s_time = (int)trim(file_get_contents(self::FIXTURE_FILE));
        return self::$s_time;
    }
}
