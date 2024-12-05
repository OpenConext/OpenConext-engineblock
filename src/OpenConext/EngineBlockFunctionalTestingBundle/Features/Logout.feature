Feature:
  In order to logout
  As an user
  I want to be able to logout of EngineBlock

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "Dummy IdP"
    And a Service Provider named "Dummy SP"

  Scenario: A user can log out
    When I log in at "Dummy SP"
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
     And I pass through EngineBlock
     And I log out at EngineBlock
    Then the url should match "logout"
     And the response should contain "Logout"
