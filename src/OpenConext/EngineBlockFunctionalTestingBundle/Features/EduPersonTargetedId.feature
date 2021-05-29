Feature:
  In order to pass a NameID to SPs that can only consume attributes
  As an OpenConext admin
  I need EB to add an EduPersonTargetedId attribute when requested in the ARP

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "TestIdp"
    And a Service Provider named "No ARP"
    And a Service Provider named "Empty ARP"
    And a Service Provider named "ARP without ePTI"
    And a Service Provider named "ARP with ePTI"
    And SP "ARP with ePTI" uses the Unspecified NameID format
    And SP "Empty ARP" allows no attributes
    And SP "ARP without ePTI" allows an attribute named "urn:mace:dir:attribute-def:uid"
    And SP "ARP with ePTI" allows an attribute named "urn:mace:dir:attribute-def:uid"
    And SP "ARP with ePTI" allows an attribute named "urn:mace:dir:attribute-def:eduPersonTargetedId"

  Scenario: As a user for an SP with an empty ARP I get no attributes (ergo no ePTI)
    When I log in at "Empty ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:eduPersonTargetedId"

  Scenario: As a user for an Idp SP without ARPxs I get all attributes, including ePTI
    When I log in at "No ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And the response should contain "urn:mace:dir:attribute-def:eduPersonTargetedId"

  Scenario: As a user for an SP with an ARP which does not contain ePTI, I do not get ePTI
    When I log in at "ARP without ePTI"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And the response should not contain "urn:mace:dir:attribute-def:eduPersonTargetedId"

  Scenario: As a user for an SP with an ARP which contains ePTI, I do get ePTI
    When I log in at "ARP with ePTI"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And the response should contain "urn:mace:dir:attribute-def:eduPersonTargetedId"

  Scenario: As a user for an SP with an ARP which contains ePTI, the EPTI is a SAML NameID
    When I log in at "ARP with ePTI"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="urn:collab:person:engine-test-stand.openconext.org:test"]'

  Scenario: Whether an ePTI is released is determined by the destination SP in case of trusted proxy
    Given SP "Step Up" is authenticating for SP "End SP"
    And SP "Step Up" is a trusted proxy
    And SP "Step Up" signs its requests
    And SP "Step Up" does not require consent
    And SP "End SP" allows an attribute named "urn:mace:terena.org:attribute-def:eduPersonTargetedId"
    When I log in at "Step Up"
    And I select "AlwaysAuth" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:eduPersonTargetedId"
