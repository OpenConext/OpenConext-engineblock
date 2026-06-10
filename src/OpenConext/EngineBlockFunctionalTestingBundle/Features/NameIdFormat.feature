Feature:
  To ensure no confusion about the NameID Format
  As EngineBlock
  I want to be sure after ARP my name id format is presented correctly to the SP

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "SSO-IdP"
    And a Service Provider named "SSO-SP"

  Scenario: EngineBlock should not update the Unspecified NameIdFormat when no ARP filters are applied
    Given SP "SSO-SP" uses the Unspecified NameID format
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"]'

  Scenario: EngineBlock should not update the Unspecified NameIdFormat when the ARP is applied
    Given SP "SSO-SP" uses the Unspecified NameID format
    And SP "SSO-SP" allows an attribute named "urn:mace:dir:attribute-def:uid"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"]'

  Scenario: EngineBlock should not update the Persistent NameIdFormat when no ARP filters are applied
    Given SP "SSO-SP" uses the Persistent NameID format
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"]'

  Scenario: EngineBlock should not update the Persistent NameIdFormat when the ARP is applied
    Given SP "SSO-SP" uses the Persistent NameID format
    And SP "SSO-SP" allows an attribute named "urn:mace:dir:attribute-def:uid"
    And SP "SSO-SP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"]'

  Scenario: EngineBlock should not update the Transient NameIdFormat when no ARP filters are applied
    Given SP "SSO-SP" uses the Transient NameID format
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"]'

  Scenario: EngineBlock should not update the Transient NameIdFormat when the ARP is applied
    Given SP "SSO-SP" uses the Transient NameID format
    And SP "SSO-SP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"]'
