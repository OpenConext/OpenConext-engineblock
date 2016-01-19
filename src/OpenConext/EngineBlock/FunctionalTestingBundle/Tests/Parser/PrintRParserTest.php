<?php

namespace OpenConext\EngineBlock\FunctionalTestingBundle\Tests\Parser;

use OpenConext\EngineBlock\FunctionalTestingBundle\Parser\PrintRParser;

class PrintRParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParser()
    {
        $content = file_get_contents(__DIR__ . '/fixture/joost.printr');

        $parser = new PrintRParser($content);
        $parsed = $parser->parse();

        $this->assertNotEmpty($parsed);

        $this->markTestIncomplete('Parser should be able to output what came in');

//        $reprinted = print_r($parsed, true);
//        $reprinted = substr($reprinted, 0, strlen($reprinted) - 1);
//        file_put_contents('/tmp/content-original', $content);
//        file_put_contents('/tmp/content-parsed', $reprinted);
//        $this->assertEquals($content, $reprinted);
    }
}
