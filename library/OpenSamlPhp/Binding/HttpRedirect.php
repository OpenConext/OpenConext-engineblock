<?php

namespace OpenSamlPhp;
namespace OpenSamlPhp\Binding;

class HttpRedirect
{
    const MESSAGE_KEY_REQUEST  = 'SAMLRequest';
    const MESSAGE_KEY_RESPONSE = 'SAMLReponse';

    public function __construct($messageType)
    {
    }

    public function getRelayState()
    {
    }

    public function getMessage()
    {
    }

    /**
     * @param \OpenSamlPhp\Http\Request $request
     * @return \OpenSamlPhp\Message
     */
    public function receive(\OpenSamlPhp\Http\Request $request)
    {

    }

    public function send(\OpenSamlPhp\Message $message)
    {
    }
}