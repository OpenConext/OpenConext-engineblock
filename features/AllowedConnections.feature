Feature: AllowedConnections
  In order to exclude and/ or include students from SP's
  As an Identity Provider
  I want to be able to blacklist certain SP's

  Background:
    Given we are using EngineBlock on the "test" environment
      And we have a "https://testsp.test.surfconext.nl/shibboleth" SP configured
      And we have a "Behat:test" IdP configured
      And the "Behat:test" IdP is configured not to allow "https://testsp.test.surfconext.nl/shibboleth"
      And the "Behat:test" IdP is configured to allow "https://teams.test.surfconext.nl/shibboleth"

  Scenario: Jasha logs in at the Test SP
    When I go to the Test SP
    Then I should not be able to select "Behat:test" from the WAYF
     But I should be able to select "SURFguest" from the WAYF
