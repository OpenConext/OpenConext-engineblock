<?php

/**
 * SetNameId command, sets the proper NameID for the Response.
 *
 * Note that because SAML2 Assertion Subject NameID elements are intended for the next hop only,
 * we don't take SP proxies into account. Whatever OUR SP wants as a NameID is what it gets.
 * If THEIR SP is known to us and wants a different NameID they'll just have to use the eduPersonTargettedId.
 */
class EngineBlock_Corto_Filter_Command_SetNameId extends EngineBlock_Corto_Filter_Command_Abstract
{
    /**
     * This command may modify the response.
     *
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Resolve what NameID we should send to our SP and set it in the Assertion.
     */
    public function execute()
    {
        $resolver = new EngineBlock_Saml2_NameIdResolver();
        $nameId = $resolver->resolve(
            $this->_request,
            $this->_response,
            $this->_serviceProvider,
            $this->_collabPersonId
        );

        $this->_response->getAssertion()->setNameId($nameId);
    }
}
