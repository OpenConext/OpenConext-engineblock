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
        $consent = $this->args['consent'];
        if (!$this->consentRepository->store($consent))
        {
            throw new Exception('Could not stored tracked login in database');
        }
    }
}