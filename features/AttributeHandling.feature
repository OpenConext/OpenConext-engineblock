Feature: Attribute Handling
  In order to make sure that I can access my federated applications
  As an end user
  I want my IdP to provide SURFconext with the mandatory attributes

  Background:
    Given we are using EngineBlock on the "test" environment
      And we have configured an "https://wrongcertidp.dev.surfconext.nl/simplesaml/saml2/idp/metadata.php" IdP that does not provide the schacHomeOrganization
      And we have a WrongAttrIdP user with the username "user", name "User" and password "password"
      And we have a IdP that returns a transient non existing uid on each login

  Scenario: User fails to log in on the Portal SP using the Wrong Attr IdP
    When I go to the Portal with "https://wrongattridp.dev.surfconext.nl/simplesaml/saml2/idp/metadata.php" as the entity ID
    And I log in at IP as "user" with password "password"
    Then EngineBlock directly gives me the error "Login failed because the institution's identity provider did not provide SURFconext with the following required attribute"

  Scenario: New User logs in and is given an unique URN containing the schacHomeOrganization
    When I go to the profile SP
    And I select from the WAYF "https://perftesttransientidp.dev.surfconext.nl/simplesaml/saml2/idp/metadata.php"
    And I log in at IP as "performancetest1" with password "password"
    And I press "I Accept"
    And I pass through EngineBlock
    Then I should see "urn:collab:person:perftestidppersistent.dev.surfconext.nl:"
    And I follow "Delete my SURFconext account!"