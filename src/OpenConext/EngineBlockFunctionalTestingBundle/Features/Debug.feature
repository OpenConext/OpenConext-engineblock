Feature:
  In order to test and debug single sign-on authentications
  As a user
  I want to perform test authentication requests on EngineBlock

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
    And an Identity Provider named "First-IdP" with logo "idp-logo.jpg"
    And an Identity Provider named "Second-IdP" with logo "idp2-logo.jpg"
    And my browser is configured to accept language "nl-NL"

  Scenario: When a user opens the debug endpoint the wayf should be presented
    When I go to Engineblock URL "/authentication/sp/debug"
    Then I should see "Selecteer een account om in te loggen bij OpenConext EngineBlock"

  Scenario: A user should be able to test a login
    When I go to Engineblock URL "/authentication/sp/debug"
    And I select "Second-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should see "Identity Provider"
    And I should see "Entity ID"
    And I should see "https://engine.vm.openconext.org/functional-testing/Second-IdP/metadata"
    And I should see "Naam"
    And I should see "Second-IdP"
    And I should see "Logo"
    And the response should contain "idp2-logo.jpg"
    And I should see "urn:mace:dir:attribute-def:uid"
    And I should see "test"
    And I should see "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And I should see "engine-test-stand.openconext.org"

  Scenario: A debug AuthnRequest should force the user to relogin
    When I go to Engineblock URL "/authentication/sp/debug"
    And I select "Second-IdP" on the WAYF
    And I pass through EngineBlock
    Then the received AuthnRequest should match xpath '/samlp:AuthnRequest[@ForceAuthn="true"]'
