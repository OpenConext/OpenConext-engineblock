Feature:
  In order to support SBS integration
  As EngineBlock
  I want to support SBS checks and merge attributes

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "SSO-IdP"
      And a Service Provider named "SSO-SP"

  Scenario: If the SBS authz check returns 'interrupt', the browser is redirected to SBS
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "interrupt" authz flow when called
#    And the sbs server will return valid attributes ## @TODO Remove this endpoint call altogether?
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the url should match "/functional-testing/interrupt"
    Given the sbs server will trigger the "authorized" authz flow when called
    And I pass through SBS
    Then the url should match "/authentication/idp/process-sraminterrupt"
    And the response should contain "Review your information that will be shared."
    And the response should contain "test_user@test.sram.surf.nl"
    And the response should contain "Proceed to SSO-SP"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
    Then the response should contain "sshkey"
    Then the response should contain "ssh_key1"

  Scenario: If the SBS authz check returns 'authorized', the attributes are merged, and the browser is not redirected.
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "authorized" authz flow when called
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the url should match "/authentication/sp/consume-assertion"
    And the response should contain "Review your information that will be shared."
    And the response should contain "test_user@test.sram.surf.nl"
    And the response should contain "Proceed to SSO-SP"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
    Then the response should contain "sshkey"
    Then the response should contain "ssh_key1"

  Scenario: If the SBS authz check returns an invalid response, the flow is halted.
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "error" authz flow when called
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the url should match "/feedback/unknown-error"
    And the response should contain "Logging in has failed"

  Scenario: If the SBS authz check returns an 'interrupt' response, and the attributes call to sbs returns an invalid response
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "interrupt" authz flow when called
#    And the sbs server will return invalid attributes ## @TODO remove attributes call?
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the url should match "/functional-testing/interrupt"
    And the sbs server will trigger the "error" authz flow when called
    And I pass through SBS
    And the response should contain "Logging in has failed"

  Scenario: If the authz call returns unknown attributes, the unknown attributes are ignored
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the 'authorized' authz flow and will return invalid attributes
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the url should match "/authentication/sp/consume-assertion"
    And the response should not contain "foo"
    And the response should not contain "baz"

  Scenario: If the sbs flow is active, other filters like PDP are still executed
    Given SP "SSO-SP" requires a policy enforcement decision
    And pdp gives an IdP specific deny response for "SSO-IdP"
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "interrupt" authz flow when called
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I should see "Error - Access denied"
    And I should see "Message from your organisation:"
    And I should see "Students of SSO-IdP do not have access to this resource"
