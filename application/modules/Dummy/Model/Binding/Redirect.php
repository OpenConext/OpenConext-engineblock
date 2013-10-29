<?php
class Dummy_Model_Binding_Redirect
    implements Dummy_Model_Binding_BindingInterface
{
    /**
     * @var SAML2_Message
     */
    private $samlMessage;

    /**
     * @var string
     */
    private $parameterName;

    /**
     * @var EngineBlock_Saml2_MessageSerializer
     */
    private $messageSerializer;

    /**
     * @param SAML2_Message $samlMessage
     * @param string $parameterName
     * @param EngineBlock_Saml2_MessageSerializer $messageSerializer
     */
    public function __construct(SAML2_Message $samlMessage, $parameterName, EngineBlock_Saml2_MessageSerializer $messageSerializer)
    {
        $this->samlMessage = $samlMessage;
        $this->parameterName = $parameterName;
        $this->messageSerializer = $messageSerializer;
    }

    public function output()
    {
        header('Location: ' . $this->createRedirectUrl());
        exit;
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    private function createRedirectUrl()
    {
        $samlMessageXml = $this->messageSerializer->serialize($this->samlMessage);


        return $this->samlMessage->getDestination() . '?' . $this->parameterName . '=' . urlencode($this->encodeSamlMessageXml($samlMessageXml));
    }

    /**
     * @param string $samlMessageXml
     * @return string
     */
    private function encodeSamlMessageXml($samlMessageXml)
    {
        return base64_encode(gzdeflate($samlMessageXml));
    }
}
