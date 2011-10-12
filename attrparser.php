<?php

/**
 *
parse string = rule , { , rule }
rule = attribute name | attribute name replace | regex |  template
template = '+' , ( attribute reference | ? all characters ? - '' )
attribute reference = '{' , attribute name , '}'
attribute name replace = attribute name , '=>' , attribute name
attribute name = ( \w | \d )

 */

class scanner
{
    private $_string = "";

    public function __construct($string)
    {
        $this->_string = $string;
    }

    
}

class parser
{
    public function __construct(scanner $scanner, array $attributes)
    {

    }
}
