Feature: Transparent Proxying
  In order to be able to let Service Providers make their own WAYF
  As an end-user
  I want to directly go to an Identity Provider without showing the EngineBlock WAYF

  Background:
    Given the "Portal" SP allows logins from "SURFguest" IdP
      But the "Portal" SP does NOT allow logins from "Feide OpenID" IdP
      And we have a SURFguest user with the username "test-boy", name "Boy" and password "test-boy"
      And we have a "Feide OpenID" user with the username "mads", name "Mads" and password "mads"

  Scenario: Boy logs in at the Portal via a Transparent Proxy Request
    When I go to the Portal with "SURFnetGuests" as the entity ID
     And I log in at Surfguest IP as "test-boy" with password "test-boy"
     And I pass through EngineBlock
    Then I should be on the Portal

  Scenario: Mads fails to log in with the Feide OpenID Idp
    When I go to the Portal with "https://xxxx.openidp.feide.no" as the entity ID
    Then I see the error "Unknown or Unusable Identity Provider"