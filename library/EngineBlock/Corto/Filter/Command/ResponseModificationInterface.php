<?php

interface EngineBlock_Corto_Filter_Command_ResponseModificationInterface
{
    /**
     * This command modifies the response attributes.
     *
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    public function getResponse();
}
