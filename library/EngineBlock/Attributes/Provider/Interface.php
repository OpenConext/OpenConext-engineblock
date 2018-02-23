<?php

interface EngineBlock_Attributes_Provider_Interface
{
    const STRATEGY_MERGE = 'merge';
    const STRATEGY_ADD   = 'add';

    const FORMAT_OPENSOCIAL = 'opensocial';
    const FORMAT_SAML       = 'saml';

    /**
     * Retrieve all attributes that the Attributes Provider provides for the
     * given user.
     * @param String $uid The URN of a user, for example
     *                    urn:collab:example.org:niels
     * @param String $format Format of the attributes to get.
     * @return Array An array containing attributes. The keys of the array are
     *               the names of the attributes. Each array element contains
     *               an array with the following elements:
     *               - format: the format of the attribute
     *               - value: the value of the attribute
     *               - source (optional): the URN of the provider of the
     *                 attribute. If source is not present, the current
     *                 AttributesProvider is the source (@see getIdentifier()).
     */
    public function getAttributes($uid, $format = self::FORMAT_SAML);

    public function getStrategy();
}
