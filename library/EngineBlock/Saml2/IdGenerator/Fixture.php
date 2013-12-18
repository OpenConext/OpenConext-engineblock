<?php

class EngineBlock_Saml2_IdGenerator_Fixture implements EngineBlock_Saml2_IdGenerator_Interface
{
    const FIXTURE_FILE = '/tmp/eb-fixtures/saml2/id';

    public function generate($prefix = 'EB')
    {
        if (file_exists(self::FIXTURE_FILE)) {
            return trim(file_get_contents(self::FIXTURE_FILE));
        }

        $defaultGenerator = new EngineBlock_Saml2_IdGenerator_Default();
        return $defaultGenerator->generate($prefix);
    }
}
