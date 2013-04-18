<?php
/**
 * Alternative for Zend_Uri::check() which does not correctly validate Uri's following the spec:
 * http://www.rfc-editor.org/errata_search.php?rfc=3986
 *
 * Note that this is a VERY permissive regex
 */
class EngineBlock_Validator_Uri
{
    const REGEX = '/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/';

    /**
     * @param string $string
     * @return bool
     */
    public function validate($uri)
    {
        return (bool) preg_match(self::REGEX, $uri);
    }
}
