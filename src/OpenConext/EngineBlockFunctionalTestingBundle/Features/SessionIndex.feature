Feature:
  In order to have a privacy safe session index in the assertion
  As EngineBlock
  I need to set the assertion id as session index

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "IP"
    And a Service Provider named "SP"

  Scenario: User logs in to SP, in that case the session index should be the assertion id
    And SP "SP" does not require consent
    When I log in at "SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I pass through EngineBlock
    And the SessionIndex should match the Assertion ID
