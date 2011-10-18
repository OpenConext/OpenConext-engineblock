Feature: Virtual Organizations
  In order to protect access to a shared Service Provider
  As a member of a Virtual Organization (a collaboration group from one or more institutions)
  I want to protect a Service Provider so that it's only be usable by members of my group.

  Background:
    Given we are using the SP "Test SP" on the "test" environment
      And we have a Group VO with the id "testsp" and group "nl:surfnet:management:testsp"
      And we have a Stem VO with the id "scn-devs" and stem "vo:scn-devs"
      And we have a Group VO with the id "rave-devs" and group "vo:scn-devs:rave-devs"
      And we have a Group VO with the id "eb-devs" and group "vo:scn-devs:eb-devs"
      And we have an Idp VO with the id "test-idps" and IdP "SURFnetGuests"
      And the SP "Test SP" is implicitly coupled to the VO "testsp"
      And we have a SURFguest user with the username "test-boy", name "Boy" and password "test-boy"
      And we have a SURFguest user with the username "test-jasha", name "Jasha" and password "test-jasha"
      And we have a SURFguest user with the username "test-ivo", name "Ivo" and password "test-ivo"
      And we have a Twitter user with the username "test-idps", name "John" and password "test-idps"
      And user "test-boy" is a member of the Group "vo:scn-devs:eb-devs"
      And user "test-jasha" is a member of the Group "vo:scn-devs:rave-devs"
      And user "test-boy" is a member of the Group "nl:surfnet:management:testsp"
      But user "test-jasha" is not a member of the Group "nl:surfnet:management:testsp"
      But user "test-ivo" is not a member of any Group
      But user "test_idps" is not a member of any Group

  Scenario: Boy logs in at the Test SP with an implicit VO
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: Ivo fails to log in at the Test SP with an implicit VO
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IdP as "test-ivo" with password "test-ivo"
    Then EngineBlock gives me the error "Membership of a Virtual Organisation required"

  Scenario: Ivo fails to log in at the Test Sp with explicit VO "scn-devs"
    When I go to the Test SP with the explicit VO "scn-devs"
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IdP as "test-ivo" with password "test-ivo"
    Then EngineBlock gives me the error "Membership of a Virtual Organisation required"

  Scenario: Boy logs in at the Test SP with explicit VO "scn-devs"
    When I go to the Test SP with the explicit VO "scn-devs"
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     Then I should be on the Test SP

  Scenario: Jasha logs in at the Test SP with explicit VO "scn-devs"
    When I go to the Test SP with the explicit VO "scn-devs"
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IdP as "test-jasha" with password "test-jasha"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: Jasha logs in at the Test SP with explicit VO "rave-devs"
    When I go to the Test SP with the explicit VO "rave-devs"
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IdP as "test-jasha" with password "test-jasha"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: Boy fails to log in at the Test SP with explicit VO "rave-devs"
    When I go to the Test SP with the explicit VO "rave-devs"
     And I select from the WAYF "SURFguest"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
    Then EngineBlock gives me the error "Membership of a Virtual Organisation required"

  Scenario: Boy logs in at the Test SP with explicit VO "test-idps"
    When I go to the Test SP with the explicit VO "test-idps"
     And I select from the WAYF "SURFnetGuests"
     And I log in at Surfguest IdP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     Then I should be on the Test SP

  Scenario: John fails to log in at the Test SP with explicit VO "test-idps"
    When I go to the Test SP with the explicit VO "test-idps"
     And I select from the WAYF "Invited Guests"
     And at the Invited Guests IdP I select "Twitter"
     And at Twitter I log in as "test_idps" with password "test-idps"
     And I pass through the Invited Guests
    Then EngineBlock gives me the error "Membership of a Virtual Organisation required"
