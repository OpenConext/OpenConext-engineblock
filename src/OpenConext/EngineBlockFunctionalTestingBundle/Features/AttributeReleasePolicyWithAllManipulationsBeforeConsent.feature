Feature:
  In order to limit leakage of unnecessary user data
  As an OpenConext admin
  I need EB to apply Attribute Release Policies

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "TestIdp"
    And a Service Provider named "No ARP"
    And a Service Provider named "Empty ARP"
    And a Service Provider named "Wildcard ARP"
    And a Service Provider named "Wrong Value ARP"
    And a Service Provider named "Right Value ARP"
    And a Service Provider named "Two value ARP"
    And SP "Empty ARP" allows no attributes
    And SP "Wildcard ARP" allows an attribute named "urn:mace:dir:attribute-def:uid"
    And SP "Wrong Value ARP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization" with value "example.edu"
    And SP "Right Value ARP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization" with value "engine-test-stand.openconext.org"
    And SP "Two value ARP" allows an attribute named "urn:mace:dir:attribute-def:uid"
    And SP "Two value ARP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And feature "eb.run_all_manipulations_prior_to_consent" is enabled

  Scenario: As a user for an Idp SP without ARPs I get all attributes
    When I log in at "No ARP"
    And I pass through EngineBlock
    And I pass through the IdP
   Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
   When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP with an empty ARP I get no attributes
    When I log in at "Empty ARP"
    And I pass through EngineBlock
    And I pass through the IdP
   Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP with a wildcard ARP I get all values for that attribute
    When I log in at "Wildcard ARP"
    And I pass through EngineBlock
    And I pass through the IdP
   Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
   When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP with a specific value ARP I do not see the attribute with a wrong value
    When I log in at "Wrong Value ARP"
    And I pass through EngineBlock
    And I pass through the IdP
   Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
   When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP with a specific value ARP I do see the attribute the right value
    When I log in at "Right Value ARP"
    And I pass through EngineBlock
    And I pass through the IdP
   Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP with 2 attributes in the ARP I only get those attributes
    When I log in at "Two value ARP"
    And I pass through EngineBlock
    And I pass through the IdP
   Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
   When I give my consent
    And I pass through EngineBlock
   Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
