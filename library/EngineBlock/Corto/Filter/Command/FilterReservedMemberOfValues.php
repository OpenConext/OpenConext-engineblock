<?php

/**
 * Remove any IDP set urn:collab:org:... only OpenConext is allowed to set these.
 */
class EngineBlock_Corto_Filter_Command_FilterReservedMemberOfValues extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    const URN_COLLAB_ORG_PREFIX = 'urn:collab:org:';
    const URN_IS_MEMBER_OF      = 'urn:mace:dir:attribute-def:isMemberOf';

    /**
     * {@inheritdoc}
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        if (!isset($this->_responseAttributes[self::URN_IS_MEMBER_OF])) {
            return;
        }

        $groups = &$this->_responseAttributes[self::URN_IS_MEMBER_OF];

        for ($i = 0; $i < count($groups); $i++) {
            $hasVoPrefix = strpos($groups[$i], self::URN_COLLAB_ORG_PREFIX) === 0;

            if (!$hasVoPrefix) {
                continue;
            }

            unset($groups[$i]);

            EngineBlock_ApplicationSingleton::getLog()->notice(
                sprintf(
                    'FilterReservedMemberOfValue: Removed "%s" value from %s attribute by %s',
                    $groups[$i],
                    self::URN_IS_MEMBER_OF,
                    $this->_identityProvider->entityId
                )
            );
        }
    }
}
