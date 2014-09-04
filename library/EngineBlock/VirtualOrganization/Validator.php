<?php

class EngineBlock_VirtualOrganization_Validator
{

    public function isMember($voId, $subjectId, $idp)
    {
        $virtualOrganization = new EngineBlock_VirtualOrganization($voId);
        $voType = $virtualOrganization->getType();

        switch ($voType) {
            case 'MIXED':
                if ($this->_isMemberOfIdps($virtualOrganization, $idp)) {
                    return true;
                }
                else if ($this->_isMemberOfGroups($virtualOrganization, $subjectId)) {
                    return true;
                }
                else {
                    return false;
                }

            case 'GROUP':
                if ($this->_isMemberOfGroups($virtualOrganization, $subjectId)) {
                    return true;
                }
                else {
                    return false;
                }

            case 'IDP':
                if ($this->_isMemberOfIdps($virtualOrganization, $idp)) {
                    return true;
                }
                else {
                    return false;
                }

            default:
                throw new EngineBlock_Exception("Unknown Virtual Organization type '$voType'");
        }
    }

    protected function _isMemberOfGroups(EngineBlock_VirtualOrganization $virtualOrganization, $subjectId)
    {
        $groups = $virtualOrganization->getGroupsIdentifiers();
        $groupValidator = new EngineBlock_VirtualOrganization_GroupValidator();
        return $groupValidator->isMember($subjectId, $groups);
    }

    protected function _isMemberOfIdps(EngineBlock_VirtualOrganization $virtualOrganization, $idp)
    {
        $idpIdentifiers = $virtualOrganization->getIdpIdentifiers();
        foreach ($idpIdentifiers as $idpId) {
            if ($idpId === $idp) {
                return true;
            }
        }
        return false;
    }

}