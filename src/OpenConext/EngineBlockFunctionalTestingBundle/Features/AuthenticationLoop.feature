Feature:
  In order to prevent wasteful resource usage
  As EngineBlock
  I want to prevent authentication loops from happening

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy Idp"
      And a Service Provider named "Dummy SP"

  @WIP
  Scenario: an authentication loop is detected
    Given EngineBlock is configured to allow a maximum of 1 authentication procedures within a time frame of 180000 seconds
    When I log in at "Dummy SP"
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
     And I pass through EngineBlock
     And I log in at "Dummy SP"
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
     And I pass through EngineBlock
     And I log in at "Dummy SP"
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
     And I pass through EngineBlock
     And I log in at "Dummy SP"
    Then I should see "Black hole"

