<?php

interface EngineBlock_AttributeProvider_Interface
{
    /**
     * Retrieve the identifier of the attribute provider
     * @return String The URN that identifies this AttributeProvider.
     */
    public function getIdentifier();

    /**
     * Retrieve all attributes that the AttributeProvider provides for the
     * given user.
     * @param String $uid The URN of a user, for example
     *                    urn:collab:surfnet.nl:niels
     * @return Array An array containing attributes. The keys of the array are
     *               the names of the attributes. Each array element contains
     *               an array with the following elements:
     *               - format: the format of the attribute
     *               - value: the value of the attribute
     *               - source (optional): the URN of the provider of the
     *                 attribute. If source is not present, the current
     *                 AttributeProvider is the source (@see getIdentifier()).
     */
    public function getAttributes($uid);
}