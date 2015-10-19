<?php

/**
 * @file
 * File: EngineBlock_Test_VirtualOrganization_GroupValidatorTest.php
 * Project: OpenConext-engineblock.
 * User: martin
 * Date: 15/10/15
 */
class EngineBlock_Test_VirtualOrganization_GroupValidatorTest
    extends PHPUnit_Framework_TestCase
{
    public function testPepValidator()
    {
        $groupValidator = new EngineBlock_VirtualOrganization_GroupValidator();
        $groups = [ 'urn:collab:group:teams.vm.openconext.org:urn:collab:group:surfnet.nl:etc:sysadmingroup' ];
        $subjectId = 'urn:collab:person:example.edu:mock1';

        $this->assertTrue($groupValidator->isMember($subjectId, $groups));
    }
}