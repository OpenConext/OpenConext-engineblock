<?php

class EngineBlock_Saml2_IdGenerator_Fixture implements EngineBlock_Saml2_IdGenerator_Interface
{
    const FIXTURE_FILE = '/tmp/eb-fixtures/saml2/id';

    /**
     * @var array
     */
    protected $frame;

    public function generate($prefix = 'EB', $usage = EngineBlock_Saml2_IdGenerator_Interface::ID_USAGE_OTHER)
    {
        if (!file_exists(self::FIXTURE_FILE)) {
            $defaultGenerator = new EngineBlock_Saml2_IdGenerator_Default();
            return $defaultGenerator->generate($prefix);
        }

        if (!isset($this->frame)) {
            // Read in the fixture data, take off a frame and write it back.
            $idFixtures = json_decode(file_get_contents(self::FIXTURE_FILE), true);
            $this->frame = array_shift($idFixtures);
            file_put_contents(self::FIXTURE_FILE, json_encode($idFixtures));
        }

        if (!isset($this->frame[$usage])) {
            throw new \RuntimeException("Unable to find a fixture for usage '$usage' in the current frame.");
        }

        return $this->frame[$usage];
    }
}
