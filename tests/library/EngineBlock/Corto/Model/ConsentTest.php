<?php
/**
 * @todo, currently only arp is tested, test all other functionality too
 */
class EngineBlock_Corto_Model_ConsentTest extends PHPUnit_Framework_TestCase
{
    /** @var EngineBlock_Corto_Model_Consent */
    private $consent;

    /** @var EngineBlock_Corto_Filter_Command_AttributeReleasePolicy */
    private $attributeReleasePolicyFilterMock;

    public function setup()
    {
        EngineBlock_ApplicationSingleton::getInstance()->bootstrap();
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();

        $this->consent = $this->factoryConsent($diContainer);
        $this->attributeReleasePolicyFilterMock = $this->factoryAttributeReleasePolicyFilter($diContainer);
    }

    public function testHasStoredConsentAppliesArp()
    {
        $serviceProviderEntityId = 'testSp';
        $spMetadata = array();
        $this->assertFalse($this->consent->hasStoredConsent($serviceProviderEntityId, $spMetadata));

        Phake::verify($this->attributeReleasePolicyFilterMock)->execute();
    }

    public function testStoreConsentAppliesArp()
    {
        $serviceProviderEntityId = 'testSp';
        $spMetadata = array();
        $this->assertFalse($this->consent->storeConsent($serviceProviderEntityId, $spMetadata));

        Phake::verify($this->attributeReleasePolicyFilterMock)->execute();
    }

    /**
     * @param EngineBlock_Application_DiContainer $diContainer
     * @return EngineBlock_Corto_Filter_Command_AttributeReleasePolicy
     */
    private function factoryAttributeReleasePolicyFilter(EngineBlock_Application_DiContainer $diContainer)
    {
        $attributeReleasePolicyFilterMock = Phake::mock('EngineBlock_Corto_Filter_Command_AttributeReleasePolicy');
        $commandFilterFactoryMock = $diContainer[EngineBlock_Application_DiContainer::FILTER_COMMAND_FACTORY];
        Phake::when($commandFilterFactoryMock)
            ->create('AttributeReleasePolicy')
            ->thenReturn($attributeReleasePolicyFilterMock);

        return $attributeReleasePolicyFilterMock;
    }

    /**
     * @param EngineBlock_Application_DiContainer $diContainer
     * @return EngineBlock_Corto_Model_Consent
     */
    private function factoryConsent(EngineBlock_Application_DiContainer $diContainer)
    {
        $tableName = null;
        $mustStoreValues = true;
        $response = array();
        $responseAttributes = array();

        $consent = new EngineBlock_Corto_Model_Consent(
            $tableName,
            $mustStoreValues,
            $response,
            $responseAttributes,
            $diContainer[EngineBlock_Application_DiContainer::FILTER_COMMAND_FACTORY],
            $diContainer[EngineBlock_Application_DiContainer::DATABASE_CONNECTION_FACTORY]
        );

        return $consent;
    }
}
