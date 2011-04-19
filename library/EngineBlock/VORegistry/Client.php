<?php

/**
 * Implementation of the Engine Block internal Virtual Organization 
 * Registry interface.
 * 
 * @author ivo
 */
class EngineBlock_VORegistry_Client
{
    /**
     * Returns an array with metadata about a Virtual Organisation
     * @param String $voIdentifier The identifier of the VO
     * @return array An array with 3 keys:
     *               - groupprovideridentifier: the identifier of the group 
     *                 directory system we need to query to find out the 
     *                 members of this VO, its groups etc.
     *               - groupidentifier: the identifier of the group in this 
     *                 group directory that contains the VO members
     *               - groupstem: if present, defines which stem in the group
     *                 directory to query. A dedicated group directory would 
     *                 not use a stem.
     */
    public function getGroupProviderMetadata($voIdentifier)
    {
        // @todo replace hardcoded values with actual lookup in VORegistry
        switch ($voIdentifier) {
            case "votest1":
                return array("groupprovideridentifier"=>"default",
                              "groupidentifier"=>"votest1group",
                	      "groupstem"=>"nl:votest1");
                break;
            case "managementvo":
                return array("groupprovideridentifier"=>"default",
                             "groupidentifier"=>"managementvotest",
                             "groupstem"=>"nl:surfnet:management");
                break;
    	    default:
                return array("groupprovideridentifier"=>"default",
                                  "groupidentifier"=>"pci_members",
                                  "groupstem"=>"nl:pci");
                break;
        }
    }
}
