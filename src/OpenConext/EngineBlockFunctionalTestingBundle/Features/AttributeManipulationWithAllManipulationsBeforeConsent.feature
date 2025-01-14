Feature:
  In order to be to influence the released attribute values
  As an IdP or SP
  I want to be able to manipulate the response attributes through configured code

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "Dummy-IdP"
    And an Identity Provider named "IdP-with-Attribute-Manipulations"
    And a Service Provider named "Dummy-SP"
    And a Service Provider named "SP-with-Attribute-Manipulations"
    And feature "eb.run_all_manipulations_prior_to_consent" is enabled

  Scenario: The Service Provider can have an attribute added
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $attributes['nl:surf:test:something'] = array("arbitrary-value");
      """
    When I log in at "SP-with-Attribute-Manipulations"
    And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "nl:surf:test:something"
    And I should see "arbitrary-value"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="nl:surf:test:something"]/saml:AttributeValue[text()="arbitrary-value"]'

  Scenario: The Service Provider can have the attributes manipulated
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $attributes['urn:mace:dir:attribute-def:uid'] = array("the-manipulated-value");
      """
    When I log in at "SP-with-Attribute-Manipulations"
    And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should see "the-manipulated-value"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:uid"]/saml:AttributeValue[text()="the-manipulated-value"]'

  Scenario: The Service Provider can have the SubjectID manipulated
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $subjectId = 'arthur.dent@domain.test';
      """
    And SP "SP-with-Attribute-Manipulations" uses the Unspecified NameID format
    When I log in at "SP-with-Attribute-Manipulations"
    And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should see "arthur.dent@domain.test"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="arthur.dent@domain.test"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="arthur.dent@domain.test"]'

  Scenario: The Service Provider cannot have the SubjectID manipulated if using a NameID format other than unspecified
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $subjectId = "arthur.dent@domain.test";
      """
    And SP "SP-with-Attribute-Manipulations" uses the Persistent NameID format
    When I log in at "SP-with-Attribute-Manipulations"
    And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should not see "arthur.dent@domain.test"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
    And the response should not match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent" and text()="arthur.dent@domain.test"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"]'
    And the response should not match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent" and text()="arthur.dent@domain.test"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"]'

  Scenario: The Service Provider cannot have the Subject NameID manipulated by setting the IntendedNameId in the reponse as it is overwritten by the subjectId
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $response['__']['IntendedNameId'] = 'NOOT';
      $subjectId = 'AAP';
      """
    And SP "SP-with-Attribute-Manipulations" uses the Unspecified NameID format
    When I log in at "SP-with-Attribute-Manipulations"
    And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should not see "NOOT"
    And I should see "AAP"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
    And the response should not match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="NOOT"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="AAP"]'
    And the response should not match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="NOOT"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="AAP"]'

  Scenario: The Service Provider can replace the NameID by setting the CustomNameID with an array representation of the NameID
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $response['__']['CustomNameId'] = array('Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient', 'Value' => 'NOOT');
      """
    And SP "SP-with-Attribute-Manipulations" uses the Unspecified NameID format
    When I log in at "SP-with-Attribute-Manipulations"
    And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should see "NOOT"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient" and text()="NOOT"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient" and text()="NOOT"]'

  Scenario: The Service Provider cannot have the SubjectID manipulated by manipulating the responseObj using the unspecified NameID Format
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $responseObj->setCollabPersonId('NOOT');
      """
    And SP "SP-with-Attribute-Manipulations" uses the Unspecified NameID format
    When I log in at "SP-with-Attribute-Manipulations"
    And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should not see "NOOT"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
    And the response should not match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="NOOT"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"]'
    And the response should not match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="NOOT"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"]'

  Scenario: The Service Provider cannot have the SubjectID manipulated by manipulating the responseObj when using a NameID Format other than unspecified
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $responseObj->setCollabPersonId('NOOT');
      """
    And SP "SP-with-Attribute-Manipulations" uses the Persistent NameID format
    When I log in at "SP-with-Attribute-Manipulations"
    And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should not see "NOOT"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
    And the response should not match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent" and text()="NOOT"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"]'
    And the response should not match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent" and text()="NOOT"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"]'

#
#  Scenario: Sp and IdP attribute manipulations
