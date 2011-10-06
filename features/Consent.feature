Feature: Consent (Backlog-136)

  Scenario: User logs into SP for the first time and has to give consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
    Then I should see "Please provide consent for SURFconext TestSP | Test | SURFnet"

  Scenario: User logs into SP for the first time and does not give consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I press "I Decline"
    Then I should see "SURFconext - No consent given"

  Scenario: User logs into SP for the first time and gives consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I press "I Accept"
     And I pass through EngineBlock
    Then print last response

  Scenario: User revokes consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
     And I go to the profile SP
    Then print last response