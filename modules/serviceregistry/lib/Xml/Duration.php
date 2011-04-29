<?php
/**
 *
 */

/**
 *
 */ 
class Xml_Duration
{
    protected $_seconds = 0;

    public static function createFromUnixTime($seconds)
    {
        return new self($seconds);
    }

    public static function createFromDuration($duration)
    {
        $parser = new Xml_Duration_Parser($duration);
        $parser->parse();
        return new self($parser->getSeconds());
    }

    protected function __construct($seconds)
    {
        $this->_seconds = $seconds;
    }

    public function getSeconds()
    {
        return $this->_seconds;
    }
}
