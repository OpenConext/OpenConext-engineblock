Feature: Single Sign On (Backlog-140)
  In order to enable Single Sign On
  As an end-user
  I only want to log in once to multiple SPs

  Scenario: BDD Test user logs out from test SP and revisits the SP immediately afterwards.
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     And I log out from the Test SP
     And I go to the Test SP
     And I pass through Surfguest IP
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: Boy logs in to SP1, then logs in to SP2 and doesn't have to fill in his credentials
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     And I go to the Portal with "SURFnetGuests" as the entity ID
     And I pass through Surfguest IP
     And I pass through EngineBlock
    Then I should be on the Portal