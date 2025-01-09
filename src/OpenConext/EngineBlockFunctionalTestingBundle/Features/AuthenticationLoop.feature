Feature:
  In order to prevent wasteful resource usage
  As EngineBlock
  I want to prevent authentication loops from happening

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And EngineBlock is configured to allow a maximum of 2 authentication procedures within a time frame of 6000 seconds
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy Idp"
      And a Service Provider named "Dummy SP"

  Scenario: an authentication loop is detected
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
    Then I should see "Black hole"

  Scenario: an authentication loop is detected when doing an unsolicited single sign on
     When An IdP initiated Single Sign on for SP "Dummy SP" is triggered by IdP "Dummy Idp"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
      And An IdP initiated Single Sign on for SP "Dummy SP" is triggered by IdP "Dummy Idp"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
      And An IdP initiated Single Sign on for SP "Dummy SP" is triggered by IdP "Dummy Idp"
     Then I should see "Black hole"

