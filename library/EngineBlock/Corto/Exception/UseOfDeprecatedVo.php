<?php

class EngineBlock_Corto_Exception_UseOfDeprecatedVo extends EngineBlock_Exception
{
    /**
     * @var string
     */
    private $virtualOrganisation;

    /**
     * EngineBlock_Corto_Exception_UseOfDeprecatedVo constructor.
     * @param string $message
     * @param $virtualOrganisation
     */
    public function __construct($message, $virtualOrganisation)
    {
        parent::__construct($message, self::CODE_NOTICE);
    }

    /**
     * @return string
     */
    public function getVirtualOrganisation()
    {
        return $this->virtualOrganisation;
    }
}
