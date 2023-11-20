Feature:
  In order to facilitate a rolling configuration update
  As EngineBlock
  I want the SP entityID that is used for Stepup auth to be configurable so that at the same time
  that the EngineBlock default key is updated, this entityID can be changed.
  This then allows two entities, with two different keys, to be configured in the Stepup-Gateway

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
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
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.vm.openconext.org/authentication/stepup/metadata"]'

  Scenario: When stepup.sfo.override_engine_entityid is configured with a valid EntityId, stepup/metadata should show that EntityId
    Given feature "eb.stepup.sfo.override_engine_entityid" is enabled
    When I go to Engineblock URL "/authentication/stepup/metadata"
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.vm.openconext.com/new/stepup/metadata"]'

  Scenario: When stepup.sfo.override_engine_entityid is configured, the the Issuer is updated
    Given the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2"
    And feature "eb.stepup.sfo.override_engine_entityid" is enabled
    When I log in at "SSO-SP"
    And I select "SSO-IdP" on the WAYF
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.vm.openconext.com/new/stepup/metadata"]'


