<?php

interface EngineBlock_Corto_Filter_Command_ResponseAttributeSourcesModificationInterface
{
    /**
     * Get metadata about origin of response attributes.
     *
     * Attributes provided by the attribute aggregator come from a specific
     * soure which engineblock must track in order to show the source on the
     * consent page.
     *
     * The sources list has the following structure:
     *
     *    attribute name => attribute source
     *
     * @param array $sources
     */
    public function getResponseAttributeSources();
}
