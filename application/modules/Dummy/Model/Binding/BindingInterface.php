<?php
interface Dummy_Model_Binding_BindingInterface
{
    /**
     * @param SAML2_Message $samlMessage
     * @param string $action
     * @param EngineBlock_Saml2_MessageSerializer $messageSerializer
     */
    public function __construct(SAML2_Message $samlMessage, $action, EngineBlock_Saml2_MessageSerializer $messageSerializer);

    public function output();
}
