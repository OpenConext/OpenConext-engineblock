Feature: Virtual Organizations
  In order to protect access to a shared Service Provider
  As a member of a Virtual Organization
  I want to a Service Provider to only be usable by members of my group.

  Background:
    Given a user with the login name "boy" and the name "Boy"
      And a user with the login name "jasha" and the name "Jasha"
      And a Group VO exists with the id "surfconext-devs" and name "SURFconext developers"
      And a Stem VO exists with the id "rave-devs" and name "Apache Rave developers"
      And the VO with id "surfconext-devs" allows users from the group "nl:surfnet:diensten:surfconext-devs"
      And the VO with id "rave-devs" allows users from the stem "rave"
      And "Boy" is a member of the VO "surfconext-devs"
      And "Jasha" is a member of the VO "rave-devs"
      And a Service Provider "SURFconext Devs Wiki" that is explicitly coupled to the VO "surfconext-devs"
      And a Service Provider "SURFconext Admin" that is implicitly coupled to the VO "surfconext-devs"
      And a Service Provider "Rave Bugtracker" that is explicitly coupled to the VO "rave-devs"

  Scenario: Boy logs in to the SURFconext Devs Wiki Service Provider
    Given I visit "https://surfconext-devs-wiki.dev.surfconext.nl/"
     When I click "explicit VO"
      And I log into
      And yet another action
     Then some testable outcome is achieved
      And something else we can check happens too