Feature:
  In order to maintain compatibility
  As Engineblock
  I want to keep LDAP-related modifications to attributes

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy IdP"
      And a Service Provider named "Dummy SP"

  Scenario: a collabPersonId is constructed from a uid with its at-signs replaced by underscores when LDAP integration is disabled
    Given feature "eb.ldap_integration" is disabled
      And the IdP "Dummy IdP" sends attribute "urn:mace:dir:attribute-def:uid" with value "frits@example.test"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:uid"]/saml:AttributeValue[text() = "frits@example.test"]'
      And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:oid:1.3.6.1.4.1.1076.20.40.40.1"]/saml:AttributeValue[text() = "urn:collab:person:engine-test-stand.openconext.org:frits_example.test"]'

  Scenario: a collabPersonId is constructed from a uid with its at-signs replaced by underscores when LDAP integration is enabled
    Given feature "eb.ldap_integration" is enabled
      And the IdP "Dummy IdP" sends attribute "urn:mace:dir:attribute-def:uid" with value "frits@example.test"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:uid"]/saml:AttributeValue[text() = "frits@example.test"]'
      And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:oid:1.3.6.1.4.1.1076.20.40.40.1"]/saml:AttributeValue[text() = "urn:collab:person:engine-test-stand.openconext.org:frits_example.test"]'
