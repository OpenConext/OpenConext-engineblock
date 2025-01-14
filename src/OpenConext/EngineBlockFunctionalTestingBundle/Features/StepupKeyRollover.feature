Feature:
  In order to facilitate a rolling configuration update
  As EngineBlock
  I want the SP entityID that is used for Stepup auth to be configurable so that at the same time
  that the EngineBlock default key is updated, this entityID can be changed.
  This then allows two entities, with two different keys, to be configured in the Stepup-Gateway

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "SSO-IdP"
    And a Service Provider named "SSO-SP"
    And an Identity Provider named "Dummy-IdP"
    And a Service Provider named "Dummy-SP"
    And a Service Provider named "Proxy-SP"

  Scenario: When stepup.sfo.override_engine_entityid is not configured, stepup/metadata should show default EntityId
    Given feature "eb.stepup.sfo.override_engine_entityid" is disabled
    When I go to Engineblock URL "/authentication/stepup/metadata"
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/authentication/stepup/metadata"]'

  Scenario: When stepup.sfo.override_engine_entityid is configured with a valid EntityId, stepup/metadata should show that EntityId
    Given feature "eb.stepup.sfo.override_engine_entityid" is enabled
    When I go to Engineblock URL "/authentication/stepup/metadata"
    Then print last response
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/new/stepup/metadata"]'

  # Note that we can not ascertain programatically if the Issuer is updated as this is an internal
  # redirect response where we can not easily intervene with the browser (we would need to disable
  # auto-following of redirects). This test does hit the code, and proves that the authentication
  # is not broken by it.
  Scenario: When stepup.sfo.override_engine_entityid is configured, the the Issuer is updated
    Given feature "eb.stepup.sfo.override_engine_entityid" is enabled
    And the SP "SSO-SP" requires Stepup LoA "http://dev.openconext.local/assurance/loa2"
    When I log in at "SSO-SP"
    And I select "SSO-IdP" on the WAYF
    And I pass through EngineBlock
    # This is where the Issuer is overridden. See: \EngineBlock_Corto_ProxyServer::sendStepupAuthenticationRequest
    And I pass through the IdP
    And Stepup will successfully verify a user with override entityID
    And I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
