<?php
class EngineBlock_Job_StoreConsent
{
    /**
     * @var EngineBlock_Corto_Model_Consent_Repository
     */
    private $consentRepository;

    public function setUp()
    {
        $databaseConnectionFactory = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getDatabaseConnectionFactory();
        $this->consentRepository = new EngineBlock_Corto_Model_Consent_Repository($databaseConnectionFactory);
    }

    public function perform()
    {
        $consent = unserialize($this->args['consent']);
        try {
            $this->consentRepository->store($consent);
        } catch (Exception $e) {
            throw new Exception('Could not store consent in database', $e->getCode(), $e);
        }
    }
}