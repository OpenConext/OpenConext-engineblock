Feature:
  In order to prevent XSS attacks
  As a user
  I need EB to filter malicious asc values in AuthnRequests

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "AlwaysAuth"
    And a Service Provider named "Malicious SP"
    And a Service Provider named "Malconfigured SP"
    And SP "Malicious SP" is set with acs location "javascript:alert('Hello world')"
    And SP "Malconfigured SP" is set with acs location "sp.example.com"

  Scenario: The Malicious SP AuthnRequest is denied by EngineBlock
    Given I log in at "Malicious SP"
    Then I should see "Error - Unsupported URI scheme in ACS location"

  Scenario: The Malconfigured SP AuthnRequest is denied by EngineBlock
    Given I log in at "Malconfigured SP"
    Then I should see "Error - Unsupported URI scheme in ACS location"
