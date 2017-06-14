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

  Scenario: As a user for an SP where no attributes are configured for aggregation
    Given SP "SP-AA" allows no attributes
    Given the attribute aggregator returns an "eduPersonOrcid" attribute
    When I log in at "SP-AA"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "eduPersonOrcid"

  Scenario: As a user for an SP where eduPersonOrcid is configured for aggregation
    Given SP "SP-AA" allows an attribute named "eduPersonOrcid"
    And the attribute aggregator returns an "eduPersonOrcid" attribute
    When I log in at "SP-AA"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "eduPersonOrcid"

  Scenario: As a user for an SP where the aggregator returns no attributes
    Given SP "SP-AA" allows an attribute named "eduPersonOrcid"
    And the attribute aggregator returns no attributes
    When I log in at "SP-AA"
    And I pass through EngineBlock
    And I pass through the IdP
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "eduPersonOrcid"
