@consent
Feature:
  In order to make an informed decision about what information I'm sharing with SPs
  As a user
  I want to send see what information the SP requires

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And an Identity Provider named "Dummy-IdP"
      And a Service Provider named "Dummy-SP"
      And SP "Dummy-SP" allows the following attributes:

        | Name                                                    | Value | Source |
        | urn:mace:dir:attribute-def:uid                          | *     |        |
        | urn:mace:terena.org:attribute-def:schacHomeOrganization | *     |        |
        | urn:mace:dir:attribute-def:cn                           | *     |        |
        | urn:mace:dir:attribute-def:displayName                  | *     |        |
        | urn:mace:dir:attribute-def:eduPersonAffiliation         | *     |        |
        | urn:mace:dir:attribute-def:eduPersonOrcid               | *     | voot   |

      And the IdP "Dummy-IdP" sends attribute "urn:mace:dir:attribute-def:cn" with value "test"
      And the IdP "Dummy-IdP" sends attribute "urn:mace:dir:attribute-def:displayName" with value "test"
      And the IdP "Dummy-IdP" sends attribute "urn:mace:dir:attribute-def:eduPersonAffiliation" with value "test"

      And SP "Dummy-SP" requires attribute aggregation
      And feature "eb.run_all_manipulations_prior_to_consent" is disabled
      And the attribute aggregator returns the attributes:

        | Name                                      |  Value | Source |
        | urn:mace:dir:attribute-def:eduPersonOrcid | 123456 | voot   |

  Scenario: The user is asked for consent to share information with the SP
    Given I log in at "Dummy-SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then the response should contain "Dummy-SP needs your information before logging in"
     Then the response should contain "support@openconext.org"
     Then the response should contain "+31612345678"
     When I give my consent
     Then I pass through EngineBlock
