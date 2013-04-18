<?php
/**
 * Alternative for Zend_Urn::check() which does not correctly validate Urn's following the spec:
 * http://www.rfc-editor.org/errata_search.php?rfc=3986
 *
 * Note that this is a VERY permissive regex
 */
class EngineBlock_Validator_Urn
{
    /**
     * RFC2141 compliant urn regex
     * based on: http://stackoverflow.com/questions/5492885/is-there-a-java-library-that-validates-urns
     */
    const REGEX = <<<'REGEX'
/^urn:[a-z0-9][a-z0-9-]{1,31}:([a-z0-9()+,-.:=@;$_!*']|%(0[1-9a-f]|[1-9a-f][0-9a-f]))+$/i
REGEX;

    /**
     * @param string $string
     * @return bool
     */
    public function validate($urn)
    {
        return (bool) preg_match(self::REGEX, $urn);
    }
}