<?php

namespace OpenSamlPhp\Binding;
interface IBinding
{
    public function getMessageXml();
    /**
     * @param \OpenSamlPhp\Http\Request $request
     * @return \OpenSamlPhp\Message
     */
    public function receive(\OpenSamlPhp\Http\Request $httpRequest);
    public function send(\OpenSamlPhp\Message $message);
}