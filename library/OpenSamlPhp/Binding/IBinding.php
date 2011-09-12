<?php

namespace OpenSamlPhp\Binding;
interface IBinding
{
    public function getMessageXml();
    /**
     * @param \OpenSamlPhp\Http\Request $request
     * @return \OpenSamlPhp\Message
     */
    public function receive(\OpenSamlPhp\Http\Request $request);
    public function send(\OpenSamlPhp\Message $message);
}