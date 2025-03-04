Feature:
  In order to find my IdP, I can find my IdP by a discovery entry
  As a user
  I want to see what information the SP requires from the discovery

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And an Identity Provider named "Dummy-IdP" with discovery "Dummy Discovery"
      And an Identity Provider named "Second IdP to trigger wayf"
      And a Service Provider named "Dummy-SP"
      And SP "Dummy-SP" allows the following attributes:

        | Name                                                    | Value | Source | Motivation                 |
        | urn:mace:dir:attribute-def:uid                          | *     |        | Motivation for uid         |
        | urn:mace:terena.org:attribute-def:schacHomeOrganization | *     |        | Motivation for sho         |
        | urn:mace:dir:attribute-def:cn                           | *     |        | Motivation for cn          |
        | urn:mace:dir:attribute-def:displayName                  | *     |        | Motivation for dn          |
        | urn:mace:dir:attribute-def:eduPersonAffiliation         | *     |        | Motivation for affiliation |
        | urn:mace:dir:attribute-def:eduPersonOrcid               | *     | voot   | Motivation for orcid       |

      And the IdP "Dummy-IdP" sends attribute "urn:mace:dir:attribute-def:cn" with value "test"
      And the IdP "Dummy-IdP" sends attribute "urn:mace:dir:attribute-def:displayName" with value "test"
      And the IdP "Dummy-IdP" sends attribute "urn:mace:dir:attribute-def:eduPersonAffiliation" with value "test"

      And SP "Dummy-SP" requires attribute aggregation
      And feature "eb.run_all_manipulations_prior_to_consent" is disabled
      And the attribute aggregator returns the attributes:

        | Name                                      |  Value | Source |
        | urn:mace:dir:attribute-def:eduPersonOrcid | 123456 | voot   |

  Scenario: The user is asked for consent to share information with the SP showing the discovery name instead of the IdP name
    Given I log in at "Dummy-SP"
    And I select IdP by label "Dummy Discovery" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "Do you agree with sharing this data?"
    And the response should not contain "Yes, proceed to Dummy-SP"
    And the response should contain "Dummy-SP will receive"
    And the response should contain "provided by  <strong>Dummy Discovery</strong>"
    And the response should contain "Proceed to Dummy-SP"
    When I give my consent
    Then I pass through EngineBlock

  Scenario: Showing the IdP name when the main IdP is used instead of the discovery
    Given I log in at "Dummy-SP"
    And I select IdP by label "Dummy-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "Do you agree with sharing this data?"
    And the response should not contain "Yes, proceed to Dummy-SP"
    And the response should contain "Dummy-SP will receive"
    And the response should contain "provided by  <strong>Dummy-IdP</strong>"
    And the response should contain "Proceed to Dummy-SP"
    When I give my consent
    Then I pass through EngineBlock
