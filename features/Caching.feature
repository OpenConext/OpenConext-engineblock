Feature: Caching (Backlog-140)
  In order to enable Single Sign On
  As an end-user
  I only want to log in once to multiple SPs

  Scenario: BDD Test user logs out from test SP.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
     And I log out from the Test SP
    Then I should see "Logout completed successfully"

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

  Scenario: BDD Test user logs out from test SP and revisits the SP immediately afterwards.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "bddtest" with password "behattest"
     And I pass through EngineBlock
     And I revoke my consent
     Then print last response