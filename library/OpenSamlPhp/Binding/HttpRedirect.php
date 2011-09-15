<?php

namespace OpenSamlPhp;
namespace OpenSamlPhp\Binding;

class HttpRedirect implements iBinding
{
    const MESSAGE_KEY_REQUEST  = 'SAMLRequest';
    const MESSAGE_KEY_RESPONSE = 'SAMLReponse';

    public function __construct($messageType)
    {
    }

    /**
     * @param \OpenSamlPhp\Http\Request $httpRequest
     * @return \OpenSamlPhp\IMessage
     */
    public function receive(\OpenSamlPhp\Http\Request $httpRequest)
    {
    }

    public function send(\OpenSamlPhp\IMessage $message)
    {
    }

    public function getRelayState()
    {
    }

    public function getMessageXml()
    {
    }
}