Feature: OpenSocial Security
  In order to protect unauthorized access to OpenSocial data
  As a SURFnet administrator
  I want to only return OpenSocial data where access is granted based on the group relations.

  Background:
    Given we are using the SP "Test SP" on the "test" environment
      And we have a Group VO with the id "testsp" and group "nl:surfnet:management:testsp"
      And the SP "Test SP" is implicitly coupled to the VO "testsp"
      And we have a SURFguest user with the username "test-boy", name "Boy" and password "test-boy"
      And we have a SURFguest user with the username "test-jasha", name "Jasha" and password "test-jasha"
      And we have a SURFguest user with the username "test-ivo", name "Ivo" and password "test-ivo"
      And user "test-boy" is a member of the Group "vo:scn-devs:eb-devs"
      And user "test-jasha" is a member of the Group "vo:scn-devs:rave-devs"
      And user "test-boy" is a member of the Group "nl:surfnet:management:testsp"
      And user "test-boy" is not a member of the Group "nl:votest1:votest1group"
      But user "test-ivo" is not a member of any Group

  Scenario: Boy logs in at the Test SP and retrieves social data from a team member
    When I go to the Test SP
     And I select from the WAYF "SURFguest (TEST)"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     And I remove my access tokens
     And I renew user oauth consent
     And I go to "https://testsp.test.surfconext.nl/testsp/openSocialQueries.shtml"
     And I retrieve the person info for "urn:collab:person:test.surfguest.nl:test-jasha"
    Then I should see "Jasha Joachimsthal"

  Scenario: Boy logs in at the Test SP and tries to retrieve social data from a non team member
    When I go to the Test SP
     And I select from the WAYF "SURFguest (TEST)"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     And I go to "https://testsp.test.surfconext.nl/testsp/openSocialQueries.shtml"
     And I retrieve the person info for "urn:collab:person:test.surfguest.nl:test-ivo"
    Then I should see "Something went wrong"

  Scenario: Boy logs in at the Test SP and retrieves members from his group
    When I go to the Test SP
     And I select from the WAYF "SURFguest (TEST)"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     And I go to "https://testsp.test.surfconext.nl/testsp/openSocialQueries.shtml"
     And I retrieve the member info for "urn:collab:group:test.surfteams.nl:nl:surfnet:diensten:testsp"
    Then I should see "urn:collab:person:perftestidppersistent.dev.surfconext.nl:bdd-user-attr"

  Scenario: Boy logs in at the Test SP and tries to retrieve members from a team where he is not a member
    When I go to the Test SP
     And I select from the WAYF "SURFguest (TEST)"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     And I go to "https://testsp.test.surfconext.nl/testsp/openSocialQueries.shtml"
     And I retrieve the member info for "urn:collab:group:test.surfteams.nl:nl:votest1:votest1group"
    Then I should see "Something went wrong"

  Scenario: Boy logs in at the Test SP and retrieves his groups
    When I go to the Test SP
     And I select from the WAYF "SURFguest (TEST)"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     And I go to "https://testsp.test.surfconext.nl/testsp/openSocialQueries.shtml"
     And I retrieve the groups info for "urn:collab:person:test.surfguest.nl:test-boy"
    Then I should see "urn:collab:group:test.surfteams.nl:nl:surfnet:diensten:testsp"

  Scenario: Boy logs in at the Test SP and tries to retrieve groups for a different person
    When I go to the Test SP
     And I select from the WAYF "SURFguest (TEST)"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     And I go to "https://testsp.test.surfconext.nl/testsp/openSocialQueries.shtml"
     And I retrieve the groups info for "urn:collab:person:test.surfguest.nl:test-jasha"
    Then I should see "Something went wrong"
    And I clean up my access tokens
