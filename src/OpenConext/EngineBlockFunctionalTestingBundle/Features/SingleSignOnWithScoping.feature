Feature:
  In order for a service provider to pre-select one or more IDPs
  As EngineBlock
  I want to limit the available IDPs in the WAYF based on ACLs or elements in the AuthnRequest

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "IDP1"
      And an Identity Provider named "IDP2"
      And an Identity Provider named "IDP3"
      And an Identity Provider named "IDP4"
      And a Service Provider named "SP"
      And a Service Provider named "remoteSP"

  Scenario: The WAYF shows only allowed IDPs
    Given SP "SP" is not connected to IdP "IDP2"
    When I log in at "SP"
    Then I should see "IDP1"
    Then I should not see "IDP2"
    Then I should see "IDP3"
    Then I should see "IDP4"

  Scenario: When only one IDP allowed, continue without WAYF
    Given SP "SP" is not connected to IdP "IDP1"
     And SP "SP" is not connected to IdP "IDP3"
     And SP "SP" is not connected to IdP "IDP4"
    When I log in at "SP"
     And I pass through EngineBlock
    Then the url should match "functional-testing/IDP2/sso"

  Scenario: The AuthnRequest is scoped to a single IDP
    Given SP "SP" scopes its request to IDP "IDP1"
    When I log in at "SP"
     And I pass through EngineBlock
    Then the url should match "functional-testing/IDP1/sso"

  Scenario: The AuthnRequest is scoped to multiple IDPs
    Given SP "SP" scopes its request to IDP "IDP3"
      And SP "SP" scopes its request to IDP "IDP4"
    When I log in at "SP"
    Then I should not see "IDP1"
    Then I should not see "IDP2"
    Then I should see "IDP3"
    Then I should see "IDP4"

  Scenario: Both connected IDPs and scoping are ANDed to limit the wayf
    Given SP "SP" scopes its request to IDP "IDP2"
      And SP "SP" scopes its request to IDP "IDP4"
     And SP "SP" is not connected to IdP "IDP3"
    When I log in at "SP"
    Then I should not see "IDP1"
    Then I should see "IDP2"
    Then I should not see "IDP3"
    Then I should see "IDP4"

  Scenario: When scoping has disallowed IDP, it's not shown
    Given SP "SP" scopes its request to IDP "IDP2"
      And SP "SP" scopes its request to IDP "IDP3"
      And SP "SP" scopes its request to IDP "IDP4"
     And SP "SP" is not connected to IdP "IDP3"
    When I log in at "SP"
    Then I should not see "IDP1"
    Then I should see "IDP2"
    Then I should not see "IDP3"
    Then I should see "IDP4"

  Scenario: Unknown RequesterID does not influence WAYF
    Given SP "SP" is authenticating and uses RequesterID "unknown-SP"
    When I log in at "SP"
    Then I should see "IDP1"
    Then I should see "IDP2"
    Then I should see "IDP3"
    Then I should see "IDP4"

  Scenario: RequesterID of SP which allows all IDPs does not influence WAYF
    Given SP "SP" is authenticating for SP "remoteSP"
    When I log in at "SP"
    Then I should see "IDP1"
    Then I should see "IDP2"
    Then I should see "IDP3"
    Then I should see "IDP4"

  Scenario: RequesterID of remote SP which has fewer IDPs limits WAYF
    Given SP "SP" is authenticating for SP "remoteSP"
     And SP "remoteSP" is not connected to IdP "IDP3"
    When I log in at "SP"
    Then I should see "IDP1"
    Then I should see "IDP2"
    Then I should not see "IDP3"
    Then I should see "IDP4"

  Scenario: Remote SP in requesterID can only limit allowed IDPs of directly authenticating SP, not extend it
    Given SP "SP" is authenticating for SP "remoteSP"
     And SP "SP" is not connected to IdP "IDP1"
     And SP "remoteSP" is not connected to IdP "IDP3"
    When I log in at "SP"
    Then I should not see "IDP1"
    Then I should see "IDP2"
    Then I should not see "IDP3"
    Then I should see "IDP4"

  Scenario: Remote SP in requesterID can only limit allowed IDPs of directly authenticating SP, not extend it with allow-all
    Given SP "SP" is authenticating for SP "remoteSP"
     And SP "SP" is not connected to IdP "IDP1"
     And SP "SP" is not connected to IdP "IDP2"
    When I log in at "SP"
    Then I should not see "IDP1"
    Then I should not see "IDP2"
    Then I should see "IDP3"
    Then I should see "IDP4"
