Feature: Caching (Backlog-140)

  @consent
  Scenario: User logs into SP for the first time and has to give consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
    Then I should see "Please provide consent for SURFconext TestSP | Test | SURFnet"

  @consent
  Scenario: User logs into SP for the first time and does not give consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I press "I Decline"
    Then I should see "SURFconext - No consent given"

  @consent
  Scenario: User logs into SP for the first time and gives consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I press "I Accept"
     And I pass through EngineBlock
    Then print last response

  @consent
  Scenario: User revokes consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
     And I go to the profile SP
    Then print last response

  @BACKLOG-140
  Scenario: BDD Test user logs out from test SP.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
     And I log out from the Test SP
    Then I should see "Logout completed successfully"

  @BACKLOG-140
  Scenario: BDD Test user logs out from test SP and revisits the SP immediately afterwards.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
     And I log out from the Test SP
     And I go to the Test SP
     And I press "Submit"
     And I pass through EngineBlock
    Then I should see "Remove Service Provider Access Tokens"

  @BACKLOG-140
  Scenario: BDD Test user logs out from test SP and revisits the SP immediately afterwards.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
     And I revoke my consent
     Then print last response