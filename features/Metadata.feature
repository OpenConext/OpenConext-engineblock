Feature: Metadata
  In order to have end-users select their Identity Provider in my layout and colors
  As a Service Provider
  I want to be able to retrieve all metadata for SURFconext Identity Providers

  Background:
    Given we are using EngineBlock on the "test" environment
      And we have a "https://testsp.test.surfconext.nl/shibboleth" SP configured
      And we have a "Behat:test" IdP configured
      And the "Behat:test" IdP is configured not to allow "https://testsp.test.surfconext.nl/shibboleth"

  Scenario: SP retrieves all metadata from Engineblock
    When I go to the metadata url of Engineblock
    Then I should retrieve all metadata for all configured IDPs including the allow-none IDP "Behat:test"

  Scenario: SP retrieves the metadata of one SP from Engineblock
    When I go to the metadata url of Engineblock with the sp-entity-id attribute with value "https://testsp.test.surfconext.nl/shibboleth"
    Then I should not retrieve the metadata for IDP "Behat:test"
     But I should retrieve the metadata for the SP "https://testsp.test.surfconext.nl/shibboleth"
