<?php

namespace OpenConext\EngineBlock\FunctionalTestingBundle\Parser;

/**
 * Parses an OpenConext-EngineBlock print_r or a SAML Response.
 * @package OpenConext\Php
 */
class PrintRParser extends AbstractSimpleParser
{
    const KEY_CHARACTERS = '\w\d:\\\/.-';

    /**
     * Parse the given content into an array.
     *
     * @return array
     */
    public function parse()
    {
        $this->debug($this->content);
        return $this->parseArray();
    }

    /**
     * @return array
     */
    protected function parseArray()
    {
        $this->debug('-> ' . __FUNCTION__);
        $this->optionalSpace();
        $this->consume('Array');
        $this->newline();
        $this->optionalSpace();
        $this->consume('\(');
        $array = array();
        $this->debug('Array START');
        while ($this->lookAhead("\n *\\[[" . static::KEY_CHARACTERS . "]+\\] =>")) {
            $array = $this->arrayContent($array);
        }
        $this->debug('Array STOP');
        $this->newline();
        $this->optionalSpace();
        $this->consume('\)');
        $this->optionalNewline();
        return $array;
    }

    /**
     * @param $array
     * @return mixed
     */
    protected function arrayContent($array)
    {
        $this->debug('-> ' . __FUNCTION__);
        $key = $this->arrayKey();
        $this->optionalSpace();
        $this->consume('=>');
        $this->optionalSpace();
        $value = $this->arrayValue();

        $array[$key] = $value;

        return $array;
    }

    /**
     * @return bool
     */
    protected function arrayKey()
    {
        $this->debug('-> ' . __FUNCTION__);
        $this->newline();
        $this->optionalSpace();
        $this->consume('\[');
        $key = $this->consume('[' . static::KEY_CHARACTERS . ']+');
        $this->consume('\]');

        return $key;
    }

    /**
     * @return array|bool|string
     */
    protected function arrayValue()
    {
        $this->debug('-> ' . __FUNCTION__);
        $this->optionalSpace();
        if ($this->lookAhead('Array')) {
            return $this->parseArray();
        }

        if ($this->lookAhead("\n")) {
            return '';
        }

        $string = $this->consume("[^\n]+");
        return $this->multiLineString($string);
    }

    /**
     * @return bool|string
     */
    protected function multiLineString($string = '')
    {
        $this->debug('-> ' . __FUNCTION__);

        $doubleNewline                  = $this->lookAhead("\n\n");
        $nextLineIsAttributeDefinition  = $this->lookAhead("[\n]{1,2} *\\[[" . static::KEY_CHARACTERS . "]+\\] =>");
        $nextLineIsArrayEnd             = $this->lookAhead("[\n]{1,2} *\\)\n");

        if ($nextLineIsAttributeDefinition || $nextLineIsArrayEnd) {
            if ($doubleNewline) {
                $this->newline();
            }
            return $string;
        }

        $string .= $this->newline();
        $string .= $this->consume('.+');

        return $this->multiLineString($string);
    }

    /**
     * @return bool
     */
    protected function optionalSpace()
    {
        $this->debug('-> ' . __FUNCTION__);
        return $this->consume(' *');
    }

    /**
     * @return bool
     */
    protected function requiredSpace()
    {
        $this->debug('-> ' . __FUNCTION__);
        return $this->consume(' +');
    }

    /**
     * @return bool
     */
    protected function newline()
    {
        $this->debug('-> ' . __FUNCTION__);
        return $this->consume("\n");
    }

    /**
     * @return bool
     */
    protected function optionalNewline()
    {
        $this->debug('-> ' . __FUNCTION__);
        if ($this->lookAhead("\n")) {
            return $this->newline();
        }
        return false;
    }
}
