<?php
/**
 * @todo, currently only arp is tested, test all other functionality too
 */
class EngineBlock_Corto_Model_ConsentTest extends PHPUnit_Framework_TestCase
{
    /** @var EngineBlock_Corto_Model_Consent */
    private $consent;

    /** @var EngineBlock_Corto_Filter_Command_AttributeReleasePolicy */
    private $attributeReleasePolicyMock;

    public function setup()
    {
        EngineBlock_ApplicationSingleton::getInstance()->bootstrap();
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();

        $tableName = null;
        $mustStoreValues = true;
        $response = array();
        $responseAttributes = array();

        $this->attributeReleasePolicyMock = Phake::mock('EngineBlock_Corto_Filter_Command_AttributeReleasePolicy');
        $consentFactoryMock = $diContainer[EngineBlock_Application_DiContainer::FILTER_COMMAND_FACTORY];
        Phake::when($consentFactoryMock)
            ->create('AttributeReleasePolicy')
            ->thenReturn($this->attributeReleasePolicyMock);

        $this->consent = new EngineBlock_Corto_Model_Consent(
            $tableName,
            $mustStoreValues,
            $response,
            $responseAttributes,
            $diContainer[EngineBlock_Application_DiContainer::FILTER_COMMAND_FACTORY],
            $diContainer[EngineBlock_Application_DiContainer::DATABASE_CONNECTION_FACTORY]
        );
    }

    public function testHasStoredConsentAppliesArp()
    {
        $serviceProviderEntityId = 'testSp';
        $spMetadata = array();
        $this->assertFalse($this->consent->hasStoredConsent($serviceProviderEntityId, $spMetadata));

        Phake::verify($this->attributeReleasePolicyMock)->execute();
    }

    public function testStoreConsentAppliesArp()
    {
        $serviceProviderEntityId = 'testSp';
        $spMetadata = array();
        $this->assertFalse($this->consent->storeConsent($serviceProviderEntityId, $spMetadata));

        Phake::verify($this->attributeReleasePolicyMock)->execute();
    }

}
