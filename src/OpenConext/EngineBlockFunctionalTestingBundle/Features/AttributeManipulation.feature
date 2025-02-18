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
    And a Service Provider named "Stepup Gateway"
    And a Service Provider named "Stepup SelfService"
    And feature "eb.run_all_manipulations_prior_to_consent" is disabled

  Scenario: The Service Provider can have an attribute added
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $attributes['nl:surf:test:something'] = array("arbitrary-value");
      """
    When I log in at "SP-with-Attribute-Manipulations"
     And I select "Dummy-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
     Then the response should not contain "nl:surf:test:something"
     And I should not see "arbitrary-value"
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
    Then I should not see "the-manipulated-value"
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
    Then I should not see "arthur.dent@domain.test"
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
    Then I should not see "AAP"
     And I should not see "NOOT"
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
    Then I should not see "NOOT"
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
     And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient" and text()="NOOT"]'
     And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient" and text()="NOOT"]'

  # See: https://www.pivotaltracker.com/story/show/159760842
  Scenario: The Service Provider can replace the NameID by setting the CustomNameID with an object representation of the NameID
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $nameId = new \SAML2\XML\saml\NameID();
      $nameId->setFormat('urn:oasis:names:tc:SAML:2.0:nameid-format:transient');
      $nameId->setValue('MIES');
      $response['__']['CustomNameId'] = $nameId;
      """
     And SP "SP-with-Attribute-Manipulations" uses the Unspecified NameID format
    When I log in at "SP-with-Attribute-Manipulations"
     And I select "Dummy-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
    Then I should not see "MIES"
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
     And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient" and text()="MIES"]'
     And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient" and text()="MIES"]'

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

  Scenario: The manipulation can access the AuthnRequest object
    Given SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $attributes['urn:mace:dir:attribute-def:uid'] = [$requestObj->getDestination()];
      """
    When I log in at "SP-with-Attribute-Manipulations"
     And I select "Dummy-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
    Then I should not see "https://engine.dev.openconext.local/authentication/idp/single-sign-on"
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulations/acs"
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:uid"]/saml:AttributeValue[text()="https://engine.dev.openconext.local/authentication/idp/single-sign-on"]'

  Scenario: The manipulation reduces a multivalued attribute to a single value
    Given the IdP "Dummy-IdP" sends attribute "urn:mace:dir:attribute-def:eduPersonAffiliation" with values "student,faculty,guest,member" and xsi:type is "xs:string"
    And SP "SP-with-Attribute-Manipulations" has the following Attribute Manipulation:
      """
      $attributes['urn:mace:dir:attribute-def:eduPersonAffiliation'] = ['guest'];
      """
    When I log in at "SP-with-Attribute-Manipulations"
     And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "eduPersonAffiliation"
    And the response should contain "faculty"
    And the response should contain "member"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:dir:attribute-def:eduPersonAffiliation"
    And the response should contain "guest"
    And the response should not contain "member"
    And the response should not contain "faculty"

  # A similar test can be found in SpProxy.feature (Scenario: User logs in via trusted proxy and attribute manipulation
  # for proxy and destination are executed). This one adds a specific test on the sequence on which they are applied.
  Scenario: As a user for an SP behind TP both AMs are applied for both services, but TP is applied first
    Given SP "Stepup Gateway" is authenticating for SP "Stepup SelfService"
    And SP "Stepup Gateway" is a trusted proxy
    And SP "Stepup Gateway" signs its requests
    And the IdP "Dummy-IdP" sends attribute "urn:mace:dir:attribute-def:eduPersonAffiliation" with values "foobar,test" and xsi:type is "xs:string"
    And SP "Stepup Gateway" has the following Attribute Manipulation:
      """
      $attributes['tps-are-applied-first'] = ['true'];
      """
    And SP "Stepup SelfService" has the following Attribute Manipulation:
      """
      $attributes['tp-is-applied-first'] = [array_key_exists('tps-are-applied-first', $attributes) ? 'yes': 'no'];
      """
    When I log in at "Stepup Gateway"
    And I select "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:eduPersonAffiliation"
    And the response should contain "test"
    And the response should contain "foobar"
    And the response should not contain "faculty"
    And the response should not contain "member"
    When I give my consent
    And I pass through EngineBlock
    Then the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="tps-are-applied-first"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="tp-is-applied-first"]/saml:AttributeValue[text()="yes"]'

