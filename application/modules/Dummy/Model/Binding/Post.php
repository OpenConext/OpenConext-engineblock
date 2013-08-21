<?php
class Dummy_Model_Binding_Post
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
     * @var EngineBlock_Saml_MessageSerializer
     */
    private $messageSerializer;

    /**
     * @param SAML2_Message $samlMessage
     * @param string $parameterName
     * @param EngineBlock_Saml_MessageSerializer $messageSerializer
     */
    public function __construct(SAML2_Message $samlMessage, $parameterName, EngineBlock_Saml_MessageSerializer $messageSerializer)
    {
        $this->samlMessage = $samlMessage;
        $this->parameterName = $parameterName;
        $this->messageSerializer = $messageSerializer;
    }

    public function output()
    {
        header('Content-Type: text/html');
        echo $this->createForm();
        exit;
    }

    /**
     * @return string
     */
    private function createForm()
    {
        $samlMessageXml = $this->messageSerializer->serialize($this->samlMessage);

        $samlMessageEncoded = $this->encodeSamlMessageXml($samlMessageXml);
        $destinationEncoded = htmlspecialchars($this->samlMessage->getDestination());
        $paraMeterNameEncoded  = htmlspecialchars($this->parameterName);

        $formHtml = <<<FORM_HTML
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <body onload="document.forms[0].submit()">
        <form action="$destinationEncoded" method="post">
            <input type="hidden" name="$paraMeterNameEncoded" value="$samlMessageEncoded"/>
            <input type="submit" value="Continue"/>
        </form>
    </body>
</html>
FORM_HTML;

        return $formHtml;
    }

    /**
     * @param string $samlMessageXml
     * @return string
     */
    private function encodeSamlMessageXml($samlMessageXml)
    {
        return base64_encode($samlMessageXml);
    }
}