Feature: Transparent Proxying
  In order to be able to let Service Providers make their own WAYF
  As an end-user
  I want to directly go to a Identity Provider without showing the EngineBlock WAYF
  Authentication using:
    - An Authentication request that points to https://[ENGINEBLOCK-HOST]/authentication/single-sign-on/abcdef1234567890
      where abcdef1234567890 is an MD5 hash of an IdP entity ID (from https://[ENGINEBLOCK-HOST]/authentication/proxy/idps-metadata).

  Background:
    Given we are using the SP "Portal SP" on the "test" environment
      And we are using a non existent SP with "https://sp.example.com/endpoint" as entity ID
      And we have a SURFguest user with the username "test-boy", name "Boy" and password "test-boy"
      And we have a SURFguest user with the username "test-jasha", name "Jasha" and password "test-jasha"
      And we have a SURFguest user with the username "test-ivo", name "Ivo" and password "test-ivo"

  Scenario: Boy logs in at the Portal via a Transparent Proxy Request
    When I go the Portal using "SURFnetGuests" as the entity ID
      And I log in as "test-boy" with password "test-boy"
      And I pass through EngineBlock
    Then I should be on the Portal

  Scenario: Boy logs in at the Portal on a non existent IdP
    When I go the Portal using "https://sp.example.com/endpoint" as the entity ID
    Then Shibboleth gives me the error "Unknown or Unusable Identity Provider"