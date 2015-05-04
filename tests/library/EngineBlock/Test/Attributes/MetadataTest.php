<?php

class EngineBlock_Test_Attributes_MetadataTest extends PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $metadata = new EngineBlock_Attributes_Metadata(
            array(
                'a' => array(
                    'Name' => array(
                        'en' => 'The a name',
                        'nl' => 'De a naam',
                    ),
                    'Description' => array(
                        'en' => 'The a desc',
                        'nl' => 'De a omsch',
                    )
                )
            ),
            new Psr\Log\NullLogger()
        );
        $this->assertEquals($metadata->getName('a')       , 'The a name', 'Get Name');
        $this->assertEquals($metadata->getDescription('a'), 'The a desc', 'Get Name');
    }
}
