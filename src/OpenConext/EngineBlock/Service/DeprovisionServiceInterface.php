<?php

namespace OpenConext\EngineBlock\Service;

interface DeprovisionServiceInterface
{
    /**
     * @param string $collabPersonId
     * @return array
     */
    public function read($collabPersonId);

    /**
     * @param string $collabPersonId
     */
    public function delete($collabPersonId);
}
