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

    /**
     * Parses the given uri with the regex, this is useful for debugging
     *
     * @param string $uri
     * @return array
     */
    public static function parse($uri)
    {
        preg_match(self::REGEX, $uri, $matches);

        $keys[] = 'match';
        $keys[] = 'scheme+separator';
        $keys[] = 'scheme';
        $keys[] = 'host+separator';
        $keys[] = 'host';
        $keys[] = 'path';
        $keys[] = 'query+separator';
        $keys[] = 'query';
        $keys[] = 'anchor+separator';
        $keys[] = 'anchor';

        $keysMatched = array_slice($keys, 0, count($matches));
        return array_combine($keysMatched, $matches);
    }
}