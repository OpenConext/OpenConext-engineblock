Feature: Metadata
  In order to build up a WAYF
  As a Service Provider
  I want to be able to retrieve all the IDP metadata
  Verifying:
    - All metadata
    - The metadata for a specific SP excluding the allow-none IDP

  Background:
    Given we are using EngineBlock on the "test" environment
      And we have several IDPs configured
      And we have a "https://portal.test.surfconext.nl/shibboleth" SP configured
      And we have a "Behat:test" IP configured
      And the "Behat:test" IP is configured to allow-none SPs

  Scenario: SP retrieves all metadata from Engineblock
    When I go to the metadata url of Engineblock
    Then I should retrieve all metadata for all configured IDPs including the allow-none IDP "Behat:test"

  Scenario: SP retrieves the metadata of one SP from Engineblock
    When I go to the metadata url of Engineblock with the sp-entity-id attribute with value "https://portal.test.surfconext.nl/shibboleth"
    Then I should not retrieve the metadata for IDP "Behat:test"
    But I should retrieve the metadata for the SP "https://portal.test.surfconext.nl/shibboleth"
