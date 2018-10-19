<?php

namespace OpenConext\EngineBlock\Metadata\Entity\Assembler;

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

/**
 * Assembler of EngineBlock roles from external metadata
 */
interface MetadataAssemblerInterface
{
    /**
     * @param mixed
     * @return AbstractRole[]
     */
    public function assemble($connections);
}
