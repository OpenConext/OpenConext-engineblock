<?php

class EngineBlock_PolicyDecisionPoint_Validator
{
    private $message;

    /**
     * @param string    $subjectId
     * @param string    $idp
     * @param string    $sp
     * @param array     $responseAttributes
     * @return bool
     */
    public function hasAccess($subjectId, $idp, $sp, $responseAttributes)
    {
        $groupValidator = new EngineBlock_PolicyDecisionPoint_PEPValidator();
        $hasAccess = $groupValidator->hasAccess($subjectId, $idp, $sp, $responseAttributes);

        // No access? Get the message.
        if (!$hasAccess)
        {
            $this->message = $groupValidator
                ->setLang('en')
                ->getMessage();
        }
        return $hasAccess;
    }

    /**
     * Get the reason/message when user has no access.
     */
    public function getMessage()
    {
        return $this->message;
    }
}