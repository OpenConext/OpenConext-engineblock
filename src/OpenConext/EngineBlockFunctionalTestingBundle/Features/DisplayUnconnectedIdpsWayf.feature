Feature:
  In order to use my preferred identity provider
  As an OpenConext user
  I need EB to show me all the unconnected identity providers in the WAYF

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And a Service Provider named "SP"
    And an Identity Provider named "Connected IdP1"
    And an Identity Provider named "Connected IdP2"
    And an Identity Provider named "Unconnected IdP1"
    And an Identity Provider named "Unconnected IdP2"
    And SP "SP" is not connected to IdP "Unconnected IdP1"
    And SP "SP" is not connected to IdP "Unconnected IdP2"

  Scenario: As a user for an SP I see only connected IdPs without request form
    Given SP "SP" is configured to only display connected IdPs in the WAYF
    When I log in at "SP"
    Then I should see "Connected IdP1"
    And I should see "Connected IdP2"
    And I should not see the "Request access" button
    And I should not see "Unconnected IdP1"
    And I should not see "Unconnected IdP2"

  Scenario: As a user for an SP I see only unconnected IdPs without request form
    Given SP "SP" is configured to display unconnected IdPs in the WAYF
    When I log in at "SP"
    Then I should see "Connected IdP1"
    And I should see "Connected IdP2"
    And I should see "Unconnected IdP1"
    And I should see "Unconnected IdP2"
    And I should see the "Request access" button
