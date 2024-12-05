Feature:
  In order to pass an identifier to the trusted proxy
  As EngineBlock
  I need to release the internal-collabPersonId attribute if a trusted proxy is involved in authentication

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "AlwaysAuth"
    And a Service Provider named "Step Up TP"
    And a Service Provider named "SelfService"

  Scenario: User logs in to SP, in that case the internalCollabPersonId should NOT be present
    Given  SP "SelfService" signs its requests
    And SP "SelfService" does not require consent
    And SP "SelfService" uses the Persistent NameID format
    When I log in at "SelfService"
    And I pass through EngineBlock
    And I pass through the IdP
    And I pass through EngineBlock
    Then the internal-collabPersonId is not present in the assertion
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"]'

  Scenario: User logs in via trusted proxy in that case the internalCollabPersonId should be present
    Given SP "Step Up TP" is authenticating for SP "SelfService"
    And SP "Step Up TP" is a trusted proxy
    And SP "Step Up TP" signs its requests
    And SP "Step Up TP" does not require consent
    And SP "Step Up TP" uses the Persistent NameID format
    And SP "SelfService" uses the Unspecified NameID format
    When I log in at "Step Up TP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I pass through EngineBlock
    Then the internal-collabPersonId is present in the assertion
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"]'


  Scenario: User logs in via trusted proxy in that case the internalCollabPersonId should be present and NameId format is not modified
    Given SP "Step Up TP" is authenticating for SP "SelfService"
    And SP "Step Up TP" is a trusted proxy
    And SP "Step Up TP" signs its requests
    And SP "Step Up TP" does not require consent
    And SP "Step Up TP" uses the Unspecified NameID format
    And SP "SelfService" uses the Transient NameID format
    When I log in at "Step Up TP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I pass through EngineBlock
    Then the internal-collabPersonId is present in the assertion
    Then the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"]'
