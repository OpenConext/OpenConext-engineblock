Feature: Virtual Organizations
  In order to protect access to a shared Service Provider
  As a member of a Virtual Organization
  I want to a Service Provider to only be usable by members of my group.
  Authentication using:
    - An explicit VO (AuthnRequest is sent to /authentication/idp/single-sing-on/vo:explicitvo)
    - An implicit VO (The SP has been forced to always use the 'implicitvo' VO in the Service Registry using the coin:default_vo_id metadata entry)
    - An SP that uses a Group VO, with both a member and a non-member of that group
    - An SP that uses a Stem VO, with both a member and a non-member of a group in the stem
    TODO: - An SP that uses an IdP VO
    TODO: Also using a VO, the SP MUST get the 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2' attribute with the proper id of the VO.

  Background:
    Given we are using the SP "Test SP" on the "test" environment
      And we have a Group VO with the id "testsp" and group "nl:surfnet:management:testsp"
      And we have a Stem VO with the id "scn-devs" and stem "vo:scn-devs"
      And we have a Group VO with the id "rave-devs" and group "vo:scn-devs:rave-devs"
      And we have a Group VO with the id "eb-devs" and group "vo:scn-devs:eb-devs"
      And the SP "Test SP" is implicitly coupled to the VO "testsp"
      And we have a SURFguest user with the username "test-boy", name "Boy" and password "test-boy"
      And we have a SURFguest user with the username "test-jasha", name "Jasha" and password "test-jasha"
      And we have a SURFguest user with the username "test-ivo", name "Ivo" and password "test-ivo"
      And user "test-boy" is a member of the Group "vo:scn-devs:eb-devs"
      And user "test-jasha" is a member of the Group "vo:scn-devs:rave-devs"
      And user "test-boy" is a member of the Group "nl:surfnet:management:testsp"
      But user "test-jasha" is not a member of the Group "nl:surfnet:management:testsp"
      But user "test-ivo" is not a member of any Group

  Scenario: Boy logs in at the Test SP with an implicit VO
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "test-boy" with password "test-boy"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: Jasha fails to log in at the Test SP with an implicit VO
    When I go to the Test SP
     And I select from the WAYF "SURFguest"
     And I log in as "test-jasha" with password "test-jasha"
    Then I should see "Membership of a Virtual Organisation required"

  Scenario: Ivo fails to log in at the Test Sp with explicit VO "scn-devs"
    When I go to the Test SP with the explicit VO "scn-devs"
     And I select from the WAYF "SURFguest"
     And I log in as "test-ivo" with password "test-ivo"
    Then I should see "Membership of a Virtual Organisation required"

  Scenario: Boy logs in at the Test SP with explicit VO "scn-devs"
    When I go to the Test SP with the explicit VO "scn-devs"
     And I select from the WAYF "SURFguest"
     And I log in as "test-boy" with password "test-boy"
     And I pass through EngineBlock
     Then I should be on the Test SP

  Scenario: Jasha logs in at the Test SP with explicit VO "scn-devs"
    When I go to the Test SP with the explicit VO "scn-devs"
     And I select from the WAYF "SURFguest"
     And I log in as "test-jasha" with password "test-jasha"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: Jasha logs in at the Test SP with explicit VO "rave-devs"
    When I go to the Test SP with the explicit VO "rave-devs"
     And I select from the WAYF "SURFguest"
     And I log in as "test-jasha" with password "test-jasha"
     And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: Boy fails to log in at the Test SP with explicit VO "rave-devs"
    When I go to the Test SP with the explicit VO "rave-devs"
     And I select from the WAYF "SURFguest"
     And I log in as "test-boy" with password "test-boy"
    Then I should see "Membership of a Virtual Organisation required"