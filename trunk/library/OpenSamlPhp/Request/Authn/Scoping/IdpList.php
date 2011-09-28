<?php

namespace OpenSamlPhp\Request\Authn\Scoping;

class IdpList
{
    /**
     * @var array
     */
    private $_idpEntries;

    /**
     * @var string
     */
    private $_getComplete;

    /**
     * @param string $getComplete
     */
    public function setGetComplete($getComplete)
    {
        $this->_getComplete = $getComplete;
    }

    /**
     * @return string
     */
    public function getGetComplete()
    {
        return $this->_getComplete;
    }

    /**
     * @param array $idpEntries
     */
    public function setIdpEntries($idpEntries)
    {
        $this->_idpEntries = $idpEntries;
    }

    /**
     * @return array
     */
    public function getIdpEntries()
    {
        return $this->_idpEntries;
    }
}