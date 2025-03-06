Feature:
  In order to support SRAM integration
  As EngineBlock
  I want to support SRAM checks and merge attributes

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "SSO-IdP"
      And a Service Provider named "SSO-SP"

  Scenario: If the SP requires SBS collaboration the SBS flow is performed
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    # Assert interrupt is called? and returns interrupt (TODO also add scenario where it returns 'authorized') and assert saml attributes are updated
    When I give my consent
    # After giving consent, the user is redirected to the interrupt
#    Then the url should match "/functional-testing/sram-interrupt"
    Then the url should match "/authentication/idp/process-sraminterrupt"
    And the response is dumped
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
    Then the response is dumped

#  It currently goes to sp, but should trip to sbs instead.
#    Then the response should not contain "Do you agree with sharing this data?"
