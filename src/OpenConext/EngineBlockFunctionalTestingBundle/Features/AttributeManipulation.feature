Feature:
  In order to be to influence the released attribute values
  As an IdP or SP
  I want to be able to manipulate the response attributes through configured code

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "Dummy-IdP"
    And an Identity Provider named "IdP-with-Attribute-Manipulations"
    And a Service Provider named "Dummy-SP"
    And a Service Provider named "SP-with-Attribute-Manipulations"

#  Scenario: No attribute manipulations
#
#  Scenario: IdP attribute manipulations
#
  Scenario: The Service Provider can have an attribute added
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $attributes['nl:surf:test:something'] = array("arbitrary value");
      """
    When I log in at "SP-with-Attribute-Manipulations"
     And I select "Dummy-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
     And the response should contain "nl:surf:test:something"

  Scenario: The Service Provider can have the attributes manipulated
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $attributes['urn:mace:dir:attribute-def:uid'] = array("the manipulated value");
      """
    When I log in at "SP-with-Attribute-Manipulations"
     And I select "Dummy-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
     And the response should contain "urn:mace:dir:attribute-def:uid"
     And the response should contain "the manipulated value"

  Scenario: The Service Provider can have the SubjectID manipulated
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $subjectId = "arthur.dent@domain.test";
      """
    And SP "SP-with-Attribute-Manipulations" uses the Unspecified NameID format
    When I log in at "SP-with-Attribute-Manipulations"
     And I select "Dummy-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
     And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"]'
     And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Value="arthur.dent@domain.test"]'
     And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"]'
     And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Value="arthur.dent@domain.test"]'

  Scenario: The Service Provider can not have the SubjectID manipulated if using a NameID format other than unspecified
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $subjectId = "arthur.dent@domain.test";
      """
    When I log in at "SP-with-Attribute-Manipulations"
     And I select "Dummy-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
     And the response should not contain "arthur.dent@domain.test"
#
#  Scenario: Sp and IdP attribute manipulations
