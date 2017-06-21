Feature:
  In order to enrich user data with attributes from various sources
  As an OpenConext admin
  I need EB to add the attributes returned by the attribute aggregator

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "IDP-AA"
    And a Service Provider named "SP-AA"
    And SP "SP-AA" requires attribute aggregation
    And feature "eb.run_all_manipulations_prior_to_consent" is disabled

  Scenario: As a user for an SP where urn:mace:dir:attribute-def:eduPersonOrcid is configured for aggregation
    Given SP "SP-AA" allows an attribute named "urn:mace:dir:attribute-def:eduPersonOrcid" and configures it for aggregation from "voot"
    And the attribute aggregator returns the attributes:

    | Name                                      |  Value | Source |
    | urn:mace:dir:attribute-def:eduPersonOrcid | 123456 | voot   |

    When I log in at "SP-AA"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:eduPersonOrcid"

  Scenario: As a user for an SP where the aggregator returns no attributes
    Given SP "SP-AA" allows an attribute named "urn:mace:dir:attribute-def:eduPersonOrcid" and configures it for aggregation from "voot"
    And the attribute aggregator returns no attributes
    When I log in at "SP-AA"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:eduPersonOrcid"

  Scenario: A user can view the attributes on the consent page grouped by attribute source
    Given SP "SP-AA" allows the following attributes:

    | Name                                              | Value | Source |
    | urn:mace:dir:attribute-def:givenName              | *     |        |
    | urn:mace:dir:attribute-def:eduPersonOrcid         | *     | voot   |
    | urn:mace:dir:attribute-def:displayName            | *     | voot   |
    | urn:mace:dir:attribute-def:cn                     | *     | sab    |
    | urn:mace:dir:attribute-def:eduPersonEntitlement   | *     | sab    |

    And the attribute aggregator returns the attributes:

    | Name                                            | Value  | Source |
    | urn:mace:dir:attribute-def:eduPersonOrcid       | 123456 | voot   |
    | urn:mace:dir:attribute-def:displayName          | test   | voot   |
    | urn:mace:dir:attribute-def:cn                   | test   | sab    |
    | urn:mace:dir:attribute-def:eduPersonEntitlement | test   | sab    |

    And the IdP "IDP-AA" sends attribute "urn:mace:dir:attribute-def:givenName" with value "test"
    When I log in at "SP-AA"
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should see the following "idp" attributes listed on the consent page:

    | Name       | Value |
    | First name | test  |

    Then I should see the following "voot" attributes listed on the consent page:

    | Name                | Value  |
    | Display Name        | test   |
    | ORCID researcher ID | 123456 |

    Then I should see the following "sab" attributes listed on the consent page:

    | Name        | Value |
    | Full Name   | test  |
    | Entitlement | test  |
