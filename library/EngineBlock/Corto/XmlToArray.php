<?php

if (!class_exists('XMLWriter')) {
    die('XMLWriter class does not exist! Please install libxml extension for php.');
}

class EngineBlock_Corto_XmlToArray
{
    const PRIVATE_PFX           = '__';
    const COMMENT_PFX           = '__c';
    const TAG_NAME_PFX          = '__t';
    const VALUE_PFX             = '__v';
    const PLACEHOLDER_VALUE     = '__placeholder__';
    const ATTRIBUTE_PFX         = '_';
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
        'urn:oasis:names:tc:SAML:2.0:metadata:ui'       => 'mdui',
        'http://www.w3.org/2001/XMLSchema-instance'     => 'xsi',
        'http://www.w3.org/2001/XMLSchema'              => 'xs',
        'http://schemas.xmlsoap.org/soap/envelope/'     => 'SOAP-ENV',
        'http://www.w3.org/2000/09/xmldsig#'            => 'ds',
        'http://www.w3.org/2001/04/xmlenc#'             => 'xenc',
        'http://www.w3.org/2001/10/xml-exc-c14n#'       => 'ec',
     );

    /**
     * @var array All XML entities which are treated as single values in Corto.
     */
    protected static $_singulars = array(
        'md:AffiliationDescriptor',
#        'md:AttributeAuthorityDescriptor',
#        'md:AuthnAuthorityDescriptor',
        'md:Company',
#        'md:EntitiesDescriptor',
#        'md:EntityDescriptor',
        'md:Extensions',
        'md:GivenName',
#        'md:IDPSSODescriptor',
        'md:Organization',
#        'md:PDPDescriptor',
#        'md:RoleDescriptor',
#        'md:SPSSODescriptor',
        'md:SurName',
        'saml:Advice',
        'saml:Assertion',             #
        'saml:AssertionIDRef',        #
        'saml:AssertionURIRef',        #
#        'saml:Attribute',
#        'saml:AttributeStatement',
        'saml:Audience',
        'saml:AudienceRestriction',
        'saml:AuthnContext',
        'saml:AuthnContextClassRef',
        'saml:AuthnContextDecl',
        'saml:AuthnContextDeclRef',
        'saml:AuthnStatement',        #
#        'saml:AuthzDecisionStatement',
        'saml:BaseID',
#        'saml:Condition',
        'saml:Conditions',
        'saml:EncryptedAssertion',    #
#        'saml:EncryptedAttribute',
        'saml:EncryptedID',
        'saml:Evidence',
        'saml:Issuer',
        'saml:NameID',
#        'saml:OneTimeUse',
#        'saml:ProxyRestriction',
#        'saml:Statement',
        'saml:Subject',
        'saml:SubjectConfirmation',
        'saml:SubjectConfirmationData',
        'saml:SubjectLocality',
        'samlp:Artifact',
        'samlp:Extensions',
        'samlp:GetComplete',
        'samlp:IDPList',
        'samlp:NameIDPolicy',
        'samlp:NewEncryptedID',
        'samlp:NewID',
        'samlp:RequestedAuthnContext',
        'samlp:Scoping',
        'samlp:Status',
        'samlp:StatusCode',
        'samlp:StatusDetail',
        'samlp:StatusMessage',
        'samlp:Terminate',
        'xenc:EncryptedData',
        'ds:CanonicalizationMethod',
        'ds:DigestMethod',
        'ds:DigestValue',
        'ds:DSAKeyValue',
        'ds:KeyInfo',
#        'ds:KeyName',
#        'ds:KeyValue',
#        'ds:MgmtData',
#        'ds:PGPData',
#        'ds:RetrievalMethod',
        'ds:RSAKeyValue',
        'ds:Signature',
        'ds:SignatureMethod',
        'ds:SignatureValue',
        'ds:SignedInfo',
#        'ds:SPKIData',
        'ds:Transforms',
#        'ds:X509Data',
        'ec:InclusiveNamespaces',
);

    protected static $_multipleValues = array(
        'saml:Attribute',
        'saml:EncryptedAttribute',
        'saml:AttributeValue',
        'samlp:IDPEntry',
        'saml:AuthenticatingAuthority',
        'samlp:RequesterID',
        'ds:X509Certificate',
        'ds:Transform',
#        'md:AssertionConsumerService',
        'md:AttributeConsumingService',
        'md:DisplayName',
        'md:EntityDescriptor',
        'md:EncryptionMethod',
        'md:KeyDescriptor',
        'md:NameIDFormat',
        'md:ServiceDescription',
        'md:ServiceName'
    );

    /**
     * Non static alias function for use in unit testable code
     *
     * @param array $attributes
     * @return array
     */
    public function attributesToArray(array $attributes) {
        return self::attributes2array($attributes);
    }

    public static function xml2array($xml)
    {
        $parser = xml_parser_create_ns();
        $foldingOptionSet = xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        if (!$foldingOptionSet) {
            throw new EngineBlock_Corto_XmlToArray_Exception(
                "Unable to set XML_OPTION_CASE_FOLDING on parser object? Error message: " . xml_error_string(xml_get_error_code($parser)),
                EngineBlock_Corto_XmlToArray_Exception::CODE_ERROR
            );
        }

        $values = array();
        $parserResultStatus = xml_parse_into_struct($parser, $xml, $values);
        if ($parserResultStatus !== 1) {
            throw new EngineBlock_Corto_XmlToArray_Exception(
                'Error parsing incoming XML. ' . PHP_EOL .
                'Error code: ' . xml_error_string(xml_get_error_code($parser)) . PHP_EOL .
                'XML: ' . $xml
            );
        }

        xml_parser_free($parser);
        self::$_singulars = array_fill_keys(self::$_singulars, 1);
        $return = self::_xml2array($values);
        self::$_singulars = array_keys(self::$_singulars);
        return $return[0];
    }

    /**
     * Convert a flat array of entities, begotten from the PHP xml_parser into a hierarchical array recursively.
     *
     * @static
     * @param array $elements
     * @param int   $level
     * @param array $namespaceMapping
     * @return array
     */

    protected static $counter = 0;

    protected static function _xml2array(&$elements, $level = 1, $namespaceMapping = array())
    {
        $newElement = array();

        while(isset($elements[self::$counter])) {
            $value = $elements[self::$counter];
            self::$counter++;

            if ($value['type'] == 'close') {
                return $newElement;
            } elseif ($value['type'] == 'cdata') {
                continue;
            }

            $hashedAttributes = array();
            $tagName = $value['tag'];
            if (isset($value['attributes']) && $attributes = $value['attributes']) {
                foreach($attributes as $attributeKey => $attributeValue) {
                    unset($attributes[$attributeKey]);
                    $hashedAttributes[self::ATTRIBUTE_PFX . $attributeKey] = $attributeValue;
                }
            }

            $complete = array();

            $tagName = self::_mapNamespacesToSaml($tagName);

            $complete[self::TAG_NAME_PFX] = $tagName;
            if ($hashedAttributes) {
                $complete = array_merge($complete, $hashedAttributes);
            }
            if (isset($value['value']) && $attributeValue = trim($value['value'])) {
                $complete[self::VALUE_PFX] = $attributeValue;
            }
            if ($value['type'] == 'open') {
                $cs = self::_xml2array($elements, $level + 1, $namespaceMapping);
                foreach($cs as $c) {
                    $tagName = $c[self::TAG_NAME_PFX];
                    unset($c[self::TAG_NAME_PFX]);

                    if (!isset(self::$_singulars[$tagName])) {
                        $complete[$tagName][] = $c;
                    } else {
                        $complete[$tagName] = $c;
                        unset($complete[$tagName][self::TAG_NAME_PFX]);
                    }
                }
            }
            $newElement[] = $complete;
        }
        self::$counter = 0;
        return $newElement;
    }

    /**
     * Maps namespace prefixes to the correct ones as used in saml
     * 
     * @param string $tagName
     * @return string
     */
    private static function _mapNamespacesToSaml($tagName)
    {
        // find prefix and elementname. Prefix is lookup of the namespace within self::_namespaces
        $fullNamespace =  substr($tagName, 0, strrpos($tagName, ':'));
        if ($fullNamespace != "") {
            // search _namespaces for namespace_prefix
            if (isset(self::$_namespaces[$fullNamespace])) {
                // prefix is found, replaces tagName with prefix:elementName
                $tagName =  self::$_namespaces[$fullNamespace] . ":" . substr($tagName, strrpos($tagName, ':') +1 );
            }
        }

        return $tagName;
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
    public static function array2xml(array $hash, $elementName = "", $useIndentation=false)
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent($useIndentation);
        $writer->setIndentString("    ");

        if (!$elementName) {
            if (isset($hash[self::TAG_NAME_PFX])) {
                $elementName = $hash[self::TAG_NAME_PFX];
            }
            else {
                throw new EngineBlock_Corto_XmlToArray_Exception("No top level tag provided or defined in hash!");
            }
        }

        self::_array2xml($hash, $elementName, $writer);

        $writer->endDocument();
        return $writer->outputMemory();
    }

    protected static function _array2xml($hash, $elementName, XMLWriter $writer, $level = 0)
    {
        if (is_array($hash) && array_key_exists(self::COMMENT_PFX, $hash)) {
            $writer->writeComment($hash[self::COMMENT_PFX]);
        }

        if ($level > self::MAX_RECURSION_LEVEL) {
            throw new EngineBlock_Corto_XmlToArray_Exception(
                'Recursion threshold exceed on element: ' . $elementName . ' for hashvalue: ' . var_export($hash, true)
            );
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
                self::_array2xml($value, $elementName, $writer, $level + 1);

            } elseif ($key === self::VALUE_PFX) {
                $writer->text($value);

            } elseif (strpos($key, self::PRIVATE_PFX) === 0) {
                # [__][<x>] is used for private attributes for internal consumption

            } elseif (strpos($key, self::ATTRIBUTE_PFX) === 0) {
                $writer->writeAttribute(substr($key, 1), $value);

            } elseif (is_array($value) || $value === self::PLACEHOLDER_VALUE) {
                self::_array2xml($value, $key, $writer, $level + 1);
            }
            else {
                throw new EngineBlock_Corto_XmlToArray_Exception(
                    "Value for key '$key' unrecognized (key naming error?)! Value" . print_r($value, true)
                );
            }
        }

        if (!isset($hash[0])) {
            $writer->endElement();
        }
    }

    /**
     * @deprecated Use XML converter from DI container and use none static method attributesToArray() instead
     * @param array $attributes
     * @return array
     * @throws EngineBlock_Corto_XmlToArray_Exception
     */
    public static function attributes2array(array $attributes)
    {
        $res = array();
        foreach($attributes as $attribute) {
            if(!isset($attribute['_Name'])) {
                throw new EngineBlock_Corto_XmlToArray_Exception('Missing attribute name');
            }

            $res[$attribute['_Name']] = array();
            if(!isset($attribute['saml:AttributeValue'])) {
                continue;
            }

            if(!is_array($attribute['saml:AttributeValue'])) {
                throw new EngineBlock_Corto_XmlToArray_Exception('AttributeValue collection is not an array');
            }

            // Add each value of the collection to the result
            foreach ($attribute['saml:AttributeValue'] as $value) {
                if(!is_array($value)) {
                    throw new EngineBlock_Corto_XmlToArray_Exception('AttributeValue is not an array');
                }

                if(!isset($value[self::VALUE_PFX])) {
                    continue;
                }

                $res[$attribute['_Name']][] = $value[self::VALUE_PFX];
            }
        }
        return $res;
    }

    public static function array2attributes($attributes)
    {
        $res = array();
        foreach((array)$attributes as $name => $attribute) {
            // Name must be a uri
            // Uri checking is hard, so at least check for a scheme.
            assert('(bool)preg_match("|(\w+)\:.+|", $name)');
            $newAttribute = array(
                '_Name' => $name,
                '_NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
            );
            foreach ((array)$attribute as $value) {
                if (is_array($value)) {
                    $newAttribute['saml:AttributeValue'][] = $value;
                }
                else {
                    $newAttribute['saml:AttributeValue'][] = array (
                       self::VALUE_PFX  => $value,
                    );
                }
            }
            $res[] = $newAttribute;
        }
        return $res;
    }

    /**
     * Format XML, adds newlines and whitespace.
     *
     * @link http://recurser.com/articles/2007/04/05/format-xml-with-php/
     *
     * @static
     * @param string $xml Unformatted XML
     * @return string Formatted XML
     */
    public static function formatXml($xml)
    {
        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

        // now indent the tags
        $token = strtok($xml, "\n");
        $result = ''; // holds formatted version as it is built
        $pad = 0; // initial indent
        $matches = array(); // returns from preg_matches()
        $indent = 0;

        // scan each line and adjust indent based on opening/closing tags
        while ($token !== false) :

            // test for the various tag states

            // 1. open and closing tags on same line - no change
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
                $indent = 0;
                // 2. closing tag - outdent now
            elseif (preg_match('/^<\/\w/', $token, $matches)) :
                $pad--;
                // 3. opening tag - don't pad this one, only subsequent tags
            elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
                $indent = 1;
                // 4. no indentation needed
            else :
                $indent = 0;
            endif;

            // pad the line with the required number of leading spaces
            $line = str_pad($token, strlen($token) + $pad, ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token = strtok("\n"); // get the next token
            $pad += $indent; // update the pad size for subsequent lines
        endwhile;

        return $result;
    }
}
