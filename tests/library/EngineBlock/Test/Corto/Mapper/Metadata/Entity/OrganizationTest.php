<?php
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
                        '_xml:lang' => 'en',
                        '__v' => 'English name',
                    ),
                    array(
                        '_xml:lang' => 'nl',
                        '__v' => 'Nederlandse naam',
                    ),
                ),
                'md:OrganizationDisplayName' => array(
                    array(
                        '_xml:lang' => 'en',
                        '__v' => 'English displayname',
                    ),
                    array(
                        '_xml:lang' => 'nl',
                        '__v' => 'Nederlandse weergavenaam',
                    )
                ),
                'md:OrganizationURL' => array(
                    array(
                        '_xml:lang' => 'en',
                        '__v' => 'English url',
                    ),
                    array(
                        '_xml:lang' => 'nl',
                        '__v' => 'Nederlandse url',
                    )
                )
            )
        );
        $this->assertEquals($expectedRootElement, $organizationMapper->mapTo($rootElement));
    }

    public function testOrganizationIsNotAddedToRootElementWhenRequiredChildElementIsNotPresent()
    {
        $entity = $this->factoryEntity();

        $childElementNames = array_keys($entity['Organization']);
        foreach($childElementNames as $childElementName) {
            $entityCopy = $entity;
            unset($entityCopy['Organization'][$childElementName]);

            $organizationMapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization($entityCopy);
            $rootElement = array();
            $this->assertEquals(array(), $organizationMapper->mapTo($rootElement));
        }

    }

    /**
     * @return array
     */
    private function factoryEntity()
    {
        $entity = array(
            'Organization' => array(
                'Name' => array(
                    'en' => 'English name',
                    'nl' => 'Nederlandse naam',

                ),
                'DisplayName' => array(
                    'en' => 'English displayname',
                    'nl' => 'Nederlandse weergavenaam',

                ),
                'URL' => array(
                    'en' => 'English url',
                    'nl' => 'Nederlandse url',
                )
            )
        );

        return $entity;
    }
}
