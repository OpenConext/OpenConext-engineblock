<?php

class EngineBlock_VirtualOrganization_Validator
{
    private $message;

    public function isMember($subjectId, $idp, $sp, $responseAttributes)
    {
        $groupValidator = new EngineBlock_VirtualOrganization_GroupValidator();
        $isMember = $groupValidator->isMember($subjectId, $idp, $sp, $responseAttributes);

        // No access? Get the message.
        if (!$isMember)
        {
            $this->message = $groupValidator
                ->setLang('en')
                ->getMessage();
        }
        return $isMember;
    }

    /**
     * Get the reason/message when user has no access.
     */
    public function getMessage()
    {
        return $this->message;
    }
}