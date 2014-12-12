<?php
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;
use OpenConext\Component\EngineBlockMetadata\Organization;

class EngineBlock_Test_Corto_Mapper_Metadata_Entity_OrganizationTest extends PHPUnit_Framework_TestCase
{
    public function testOrganizationIsCorrectlyAddedToRootElement()
    {
        $entity = $this->factoryEntity();
        $organizationMapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization($entity);
        $rootElement = array();

        $expectedRootElement = array(
            'md:Organization' => array(
                'md:OrganizationName' => array(
                    array(
                        '_xml:lang' => 'nl',
                        '__v' => 'Nederlandse naam',
                    ),
                    array(
                        '_xml:lang' => 'en',
                        '__v' => 'English Name',
                    ),
                ),
                'md:OrganizationDisplayName' => array(
                    array(
                        '_xml:lang' => 'nl',
                        '__v' => 'Nederlandse weergavenaam',
                    ),
                    array(
                        '_xml:lang' => 'en',
                        '__v' => 'English displayname',
                    ),
                ),
                'md:OrganizationURL' => array(
                    array(
                        '_xml:lang' => 'nl',
                        '__v' => 'Nederlandse url',
                    ),
                    array(
                        '_xml:lang' => 'en',
                        '__v' => 'English url',
                    ),
                )
            )
        );
        $this->assertEquals($expectedRootElement, $organizationMapper->mapTo($rootElement));
    }

    /**
     * @return IdentityProviderEntity
     */
    private function factoryEntity()
    {
        $entity = new IdentityProviderEntity('https://idp.example.edu');
        $entity->organizationEn = new Organization('English Name', 'English displayname', 'English url');
        $entity->organizationNl = new Organization('Nederlandse naam', 'Nederlandse weergavenaam', 'Nederlandse url');

        return $entity;
    }
}
