<?php

/**
 *
 *
 * @package    Corto
 * @module     Library
 * @author     Mads Freek Petersen, <freek@ruc.dk>
 * @author     Boy Baukema, <boy@ibuildings.com>
 * @licence    MIT License, see http://www.opensource.org/licenses/mit-license.php
 * @copyright  2009-2010 WAYF.dk
 * @version    $Id:$
 */

class Corto_XmlToHash
{
    const PRIVATE_KEY_PREFIX    = '__';
    /**
     * array('__t'=>'books', 'book'=>array(array('__v'=>'Mijn boek')))
     * <>
     */
    const TAG_NAME_KEY          = '__t';
    const VALUE_KEY             = '__v';
    const PLACEHOLDER_VALUE     = '__placeholder__';
    const ATTRIBUTE_KEY_PREFIX  = '_';
    const MAX_RECURSION_LEVEL   = 50;

    /**
     * @var array All namespaces used in SAML2 messages.
     */
    protected static $_namespaces = array(
        'urn:oasis:names:tc:SAML:1.0:protocol'          => 'samlp',
        'urn:oasis:names:tc:SAML:1.0:assertion'         => 'saml',
        'urn:oasis:names:tc:SAML:2.0:protocol'          => 'samlp',
        'urn:oasis:names:tc:SAML:2.0:assertion'         => 'saml',
        'urn:oasis:names:tc:SAML:2.0:metadata'          => 'md',
        'http://www.w3.org/2001/XMLSchema-instance'     => 'xsi',
        'http://www.w3.org/2001/XMLSchema'              => 'xs',
        'http://schemas.xmlsoap.org/soap/envelope/'     => 'SOAP-ENV',
        'http://www.w3.org/2000/09/xmldsig#'            => 'ds',
        'http://www.w3.org/2001/04/xmlenc#'             => 'xenc',
     );

    /**
     * @var array All XML entities which are allowed to have multiple values in Corto
     */
    protected static $_multipleValues = array(
        'saml:Attribute',
        'saml:EncryptedAttribute',
        'saml:AttributeValue',
        'samlp:IDPEntry',
        'saml:AuthenticatingAuthority',
        'samlp:RequesterID',
        'ds:X509Certificate',
        'Transform',
        'md:EntityDescriptor',
        'md:KeyDescriptor',
    );

    public static function xml2hash($xml)
    {
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        $parserResultStatus = xml_parse_into_struct($parser, $xml, $values);
        if ($parserResultStatus !== 1) {
            die("Error parsing incoming XML: " .
                    xml_error_string(xml_get_error_code($parser)) .
                    "<pre>\n" . htmlspecialchars($xml));
        }

        xml_parser_free($parser);
        $return = self::_xml2hash($values);
        return $return[0];
    }

    /**
     * Convert a flat array of entities, begotten from the PHP xml_parser into a hirarchical array recursively.
     *
     * @static
     * @param array $elements
     * @param int   $level
     * @param array $namespaceMapping
     * @return array
     */
    protected static function _xml2hash(&$elements, $level = 1, $namespaceMapping = array())
    {
        $newElement = array();
        while($value = array_shift($elements)) {
            if ($value['type'] == 'close') {
                return $newElement;
            } elseif ($value['type'] == 'cdata') {
                continue;
            }

            $hashedAttributes = array();
            if (isset($value['attributes']) && $attributes = $value['attributes']) {
                foreach($attributes as $attributeKey => $attributeValue) {
                    unset($attributes[$attributeKey]);

                    if (preg_match("/^xmlns:(.+)$/", $attributeKey, $namespacePrefixAndTag)) {
                        $namespaceMapping[$namespacePrefixAndTag[1]] = self::$_namespaces[$attributeValue];
                        $hashedAttributes['_xmlns:'.self::$_namespaces[$attributeValue]] = $attributeValue;
                    } else {
                        $hashedAttributes[self::ATTRIBUTE_KEY_PREFIX . $attributeKey] = $attributeValue;
                    }
                }
            }
            $complete = array();
            $tagName = $value['tag'];
            if (preg_match("/^(.+):(.+)$/", $tagName, $namespacePrefixAndTag) && $prefix = $namespaceMapping[$namespacePrefixAndTag[1]]) {
                $tagName = $prefix . ":" . $namespacePrefixAndTag[2];
            }
            $complete[self::TAG_NAME_KEY] = $tagName;
            if ($hashedAttributes) {
                $complete = array_merge($complete, $hashedAttributes);
            }
            if (isset($value['value']) && $attributeValue = trim($value['value'])) {
                $complete[self::VALUE_KEY] = $attributeValue;
            }
            if ($value['type'] == 'open') {
                $cs = self::_xml2hash($elements, $level + 1, $namespaceMapping);
                foreach($cs as $c) {
                    $tagName = $c[self::TAG_NAME_KEY];
                    unset($c[self::TAG_NAME_KEY]);
                    if (in_array($tagName, self::$_multipleValues)) {
                        $complete[$tagName][] = $c;
                    } else {
                        $complete[$tagName] = $c;
                        unset($complete[$tagName][self::TAG_NAME_KEY]);
                    }
                }
            } elseif ($value['type'] == 'complete') {
            }
            $newElement[] = $complete;
        }
        return $newElement;
    }

    /**
     * Convert a hash (array) to XML.
     *
     * Example:
     * hash2xml(array('book'=>array('_id'=>'1','title'=>array('__v'=>'SAML For beginners'))), 'catalog');
     * Converts to:
     * <catalog><book id='1'><title>SAML For Beginners</title></book></catalog>
     *
     * @static
     * @param array  $hash        Hash/array to convert
     * @param string $elementName Specific element to convert, if empty then the top level element is used
     * @return string XML from array
     */
    public static function hash2xml(array $hash, $elementName = "", $useIndentation=false)
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0');
        $writer->setIndent($useIndentation);
        $writer->setIndentString("    ");

        if (!$elementName) {
            if (isset($hash[self::TAG_NAME_KEY])) {
                $elementName = $hash[self::TAG_NAME_KEY];
            }
            else {
                throw new Exception("No top level tag provided or defined in hash!");
            }
        }

        self::_hash2xml($hash, $elementName, $writer);

        $writer->endDocument();
        return $writer->outputMemory();
    }

    protected static function _hash2xml($hash, $elementName, XMLWriter $writer, $level = 0)
    {
        if ($level > self::MAX_RECURSION_LEVEL) {
            throw new Exception('Recursion threshhold exceed on element: '.$elementName . ' for hashvalue: ' . var_export($hash, true));
        }
        if ($hash == self::PLACEHOLDER_VALUE) {
            // Ignore placeholders
            return;
        }
        if (!isset($hash[0])) {
            $writer->startElement($elementName);
        }

        foreach((array)$hash as $key => $value) {
            if (is_int($key)) {
                // Normal numeric index, value is probably a hash structure, recurse...
                self::_hash2xml($value, $elementName, $writer, $level + 1);

            } elseif ($key === self::VALUE_KEY) {
                $writer->text($value);

            } elseif (strpos($key, self::PRIVATE_KEY_PREFIX) === 0) {
                # [__][<x>] is used for private attributes for internal consumption

            } elseif (strpos($key, self::ATTRIBUTE_KEY_PREFIX) === 0) {
                $writer->writeAttribute(substr($key, 1), $value);

            } else {
                self::_hash2xml($value, $key, $writer, $level + 1);
            }
        }

        if (!isset($hash[0])) {
            $writer->endElement();
        }
    }

    public static function attributes2hash($attributes)
    {
        foreach((array)$attributes as $attribute) {
            foreach ($attribute['saml:AttributeValue'] as $value) {
                $res[$attribute['_Name']][] = $value[self::VALUE_KEY];
            }
        }
        return $res;
    }

    public static function hash2attributes($attributes)
    {
        foreach((array)$attributes as $name => $attribute) {
            $newAttribute = array(
                '_Name' => $name,
                '_NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:basic',
            );
            foreach ((array)$attribute as $value) {
                $newAttribute['saml:AttributeValue'][] = array (
                   '_xsi:type'      => 'xs:string',
                   self::VALUE_KEY  => $value,
                );
            }
            $res[] = $newAttribute;
        }
        return $res;
    }
}