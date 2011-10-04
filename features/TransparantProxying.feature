Feature: Transparent Proxying
  In order to be able to let Service Providers make their own WAYF
  As an end-user
  I want to directly go to a Identity Provider without showing the EngineBlock WAYF
  Authentication using:
      An Authentication request that points to https://[ENGINEBLOCK-HOST]/authentication/single-sign-on/abcdef1234567890
      where abcdef1234567890 is an MD5 hash of an IdP entity ID (from https://[ENGINEBLOCK-HOST]/authentication/proxy/idps-metadata).

  Background:
    Given the "Portal" SP allows logins from "SURFguest" IdP
      And we have a SURFguest user with the username "test-boy", name "Boy" and password "test-boy"
      And we have a SURFguest user with the username "test-jasha", name "Jasha" and password "test-jasha"
      And we have a SURFguest user with the username "test-ivo", name "Ivo" and password "test-ivo"
    But the "Portal" SP does NOT allow logins from "Feide OpenID" IdP

  Scenario: Boy logs in at the Portal via a Transparent Proxy Request
    When I go the Portal with "SURFnetGuests" as the entity ID
      And I log in as "test-boy" with password "test-boy"
      And I pass through EngineBlock
    Then I should be on the Portal

  Scenario: Mads fails to log in with the Feide OpenID Idp
    When I go the Portal with "https://openid.feide.no" as the entity ID
    Then I see the error "Unknown or Unusable Identity Provider"