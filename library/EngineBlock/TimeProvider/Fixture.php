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
        $fixture = new \OpenConext\Component\EngineBlockFixtures\TimeFixture(
            new \OpenConext\Component\EngineBlockFixtures\DataStore\JsonDataStore(
                static::FIXTURE_FILE
            )
        );
        return $fixture->get();
    }
}
