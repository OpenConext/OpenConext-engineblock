Feature: Consent (Backlog-136)
  In order to offer end-users control of their personal data
  As an end-user
  I want to explicitly consent to the release of my data to a Service Provider

  Background:
    Given we have a SURFguest user with the username "bddtest", name "Behavior" and password "behattest"

  Scenario: User logs into SP for the first time and has to give consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "bddtest" with password "behattest"
    Then I should see "Please provide consent"

  Scenario: User logs into SP for the first time and does not give consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "bddtest" with password "behattest"
     And I press "I Decline"
    Then I should see "No consent given"

  Scenario: User logs into SP for the first time and gives consent.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "bddtest" with password "behattest"
     And I press "I Accept"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: User logs into SP for the second time and does not have to give consent
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "bddtest" with password "behattest"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: User logs into a second SP and has to give consent again
    When I go to the Portal with "SURFnetGuests" as the entity ID
     And I log in at Surfguest IP as "bddtest" with password "behattest"
     And I press "I Accept"
     And I pass through EngineBlock
    Then I should be on the Portal

  Scenario: User goes back to first SP and still doesn't have to give consent
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "bddtest" with password "behattest"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: User revokes consent.
    When I go to the profile SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "bddtest" with password "behattest"
     And I press "I Accept"
     And I pass through EngineBlock
     And I follow "Delete my SURFconext account!"

     And I go to the Test SP
     And I pass through SURFguest
    Then I should see "Please provide consent"

  Scenario: User logs into SP and has to give consent again.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "bddtest" with password "behattest"
    Then I should see "Please provide consent"

  Scenario: User changes his surname and has to give consent again.
    When I go to "https://test.surfguest.nl/user/edit"
     And I log in at SURFguest as "bddtest" with password "behattest"
     And I fill in "abcdef" for "last_name"
     And I press "edit"
     And I check for form errors

     And I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "bddtest" with password "behattest"
    Then I should see "Please provide consent"

  Scenario: User changes his surname back and still has to give consent.
    When I go to "https://test.surfguest.nl/user/edit"
     And I log in at SURFguest as "bddtest" with password "behattest"
     And I fill in "Development" for "last_name"
     And I press "edit"
     And I check for form errors

     And I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "bddtest" with password "behattest"
    Then I should see "Please provide consent"

