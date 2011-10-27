Feature: AllowedConnections
  In order to exclude and/ or include students from SP's
  As a Identity Provider
  I want to be able to blacklist certain SP's
  Verifying:
    - If a IdP has blacklisted a SP, or vice versa, it should not be visible in the WAYF.

  Background:
    Given we are using EngineBlock on the "test" environment
      And we have a "https://testsp.test.surfconext.nl/shibboleth" SP configured
      And we have a "Behat:test" IdP configured
      And the "Behat:test" IdP is configured not to allow "https://testsp.test.surfconext.nl/shibboleth"
      And the "Behat:test" IdP is configured to allow "https://teams.test.surfconext.nl/shibboleth"
      And the "perftestpersistentidp" IdP is configured to allow "https://teams.test.surfconext.nl/shibboleth"
      And the "perftestpersistentidp" IdP is configured not allow "SURFconext Profile"

  Scenario: Jasha logs in at the Test SP
    When I go to the Test SP
    Then I should not be able to select "Behat:test" from the WAYF
    And I should be able to select "SURFguest (TEST)" from the WAYF
    And I select from the WAYF "SURFguest (TEST)"
    And I log in at Surfguest IdP as "test-jasha" with password "test-jasha"
    And I pass through EngineBlock
    Then I should be on the Test SP

  Scenario: Performance tester logs in at the Portal SP
    When I go to the Portal with "https://perftestpersistentidp.dev.surfconext.nl/simplesaml/saml2/idp/metadata.php" as the entity ID
    And I log in at IdP as "performancetest1" with password "password"
    And I pass through EngineBlock
    And I should be on the Portal
    #Trying to transparantly login
    Then I visit "https://profile.test.surfconext.nl/"
    And I should should be on the WAYF
    And I should not be able to select "https://perftestpersistentidp.dev.surfconext.nl/simplesaml/saml2/idp/metadata.php" from the WAYF
