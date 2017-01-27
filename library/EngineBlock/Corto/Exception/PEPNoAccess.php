<?php

use OpenConext\EngineBlockBundle\Pdp\PolicyDecision;

class EngineBlock_Corto_Exception_PEPNoAccess extends EngineBlock_Exception
{
    /**
     * @var PolicyDecision
     */
    private $policyDecision;

    public function __construct($message, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }

    public static function basedOn($policyDecision)
    {
        $exception = new self('Access denied after policy enforcement');
        $exception->policyDecision = $policyDecision;

        return $exception;
    }

    /**
     * @return PolicyDecision
     */
    public function getPolicyDecision()
    {
        return $this->policyDecision;
    }
}
