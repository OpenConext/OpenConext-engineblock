<?php
class EngineBlock_Corto_Module_Service_Metadata_BindingsReplacerTest
    extends PHPUnit_Framework_TestCase
{
    public function testBindingsAreReplaced()
    {
        $entity = array(
            'SingleSignOn' => array(
                array(
                    'Binding' => 'testBinding',
                    'Location' => 'testLocation'
                )
            )
        );
        $replacer = new EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer($entity);
        $replacer->replace('SingleSignOn', 'newLocation', array(
            'newBinding1',
            'newBinding2'
        ));

        $expectedBinding = array(
            array(
                'Binding' => 'newBinding1',
                'Location' => 'newLocation'
            ),
            array(
                'Binding' => 'newBinding2',
                'Location' => 'newLocation'
            )
        );
        $this->assertEquals($expectedBinding, $entity['SingleSignOn']);
    }

    public function testBindingsAreAdded()
    {
        $entity = array();
        $replacer = new EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer($entity);
        $replacer->replace('SingleSignOn', 'newLocation', array(
            'newBinding1',
        ));

        $expectedBinding = array(
            array(
                'Binding' => 'newBinding1',
                'Location' => 'newLocation'
            )
        );
        $this->assertEquals($expectedBinding, $entity['SingleSignOn']);
    }

}