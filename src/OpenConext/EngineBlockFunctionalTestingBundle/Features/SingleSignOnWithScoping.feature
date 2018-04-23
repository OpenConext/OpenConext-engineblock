Feature:
  In order for a service provider to pre-select one or more IDPs
  As EngineBlock
  I want to limit the available IDPs in the WAYF based on the scoping in the AuthnRequest

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "IDP1"
      And an Identity Provider named "IDP2"
      And an Identity Provider named "IDP3"
      And an Identity Provider named "IDP4"
      And a Service Provider named "SP"

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
