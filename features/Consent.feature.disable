Feature: Consent (Backlog-136)
  In order to control

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
    Then I should be on the Test SP

  Scenario: User logs into SP for the second time and does not have to give consent
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: User logs into a second SP and has to give consent again
    When I go the Portal with "SURFnetGuests" as the entity ID
     And I log in as "bddtest" with password "behattest"
     And I press "I Accept"
     And I pass through EngineBlock
    Then I should be on the Portal

  Scenario: User goes back to first SP and still doesn't have to give consent
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: User revokes consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
     And I go to the profile SP
     And I pass through SURFguest
     And I press "I Accept"
     And I pass through EngineBlock
     And I follow "Delete my SURFconext account!"