<?php
class Dummy_Model_Binding_BindingFactory
{
    const TYPE_REDIRECT = 'redirect';
    const TYPE_POST = 'post';

    /**
     * @var array
     */
    private $supportedBindings = array(
        self::TYPE_REDIRECT,
        self::TYPE_POST
    );

    /**
     * @var EngineBlock_Saml_MessageSerializer
     */
    private $messageSerializer;

    public function __construct()
    {
        // @todo get via dependency injection
        $this->messageSerializer = new EngineBlock_Saml_MessageSerializer();
    }

    /**
     * @param SAML2_Message $samlMessage
     * @param string $type
     * @return Dummy_Model_Binding_BindingInterface
     * @throws InvalidArgumentException
     */
    public function create(SAML2_Message $samlMessage, $type)
    {
        $parameterName = $this->getParameterNameFromMessage($samlMessage);
        if (!in_array($type, $this->supportedBindings)) {
            throw new InvalidArgumentException("Invalid Binding type '$type'");
        }

        $bindingClass = 'Dummy_Model_Binding_' . ucfirst($type);
        return new $bindingClass($samlMessage, $parameterName, $this->messageSerializer);
    }

    /**
     * @param SAML2_Message $samlMessage
     * @return string
     * @throws InvalidArgumentException
     */
    private function getParameterNameFromMessage(SAML2_Message $samlMessage)
    {
        if ($samlMessage instanceof SAML2_AuthnRequest) {
            return 'SAMLRequest';
        } elseif ($samlMessage instanceof SAML2_Response) {
            return 'SAMLResponse';
        }

        throw new InvalidArgumentException('Unknown Saml message ' . get_class($this->samlMessage));
    }
}