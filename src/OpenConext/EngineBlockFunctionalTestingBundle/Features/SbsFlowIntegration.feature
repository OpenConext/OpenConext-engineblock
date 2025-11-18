Feature:
  In order to support SRAM integration
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
    And the sbs server will return valid attributes
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the url should match "/functional-testing/interrupt"
    And I pass through SBS
    Then the url should match "/authentication/idp/process-sraminterrupt"
    And the response should contain "Review your information that will be shared."
    And the response should contain "test_user@test.sram.surf.nl"
    And the response should contain "Proceed to SSO-SP"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
    Then the response should contain "ssh_key1"
    And the response should contain "ssh_key2"

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
    Then the response should contain "ssh_key1"
    And the response should contain "ssh_key2"

  Scenario: If the SBS authz check returns an invalid response, the flow is halted.
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "error" authz flow when called
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the url should match "/feedback/unknown-error"
    And the response should contain "Logging in has failed"

  Scenario: Stepup authentication combined with SBS 'authorized' flow merges attributes and shows them on consent screen
    Given the SP "SSO-SP" requires Stepup LoA "http://dev.openconext.local/assurance/loa2"
    And the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "authorized" authz flow when called
    And the sbs server will return valid attributes
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And Stepup will successfully verify a user
    Then the url should match "/authentication/stepup/consume-assertion"
    And the response should contain "Review your information that will be shared."
    And the response should contain "test_user@test.sram.surf.nl"
    And the response should contain "ssh_key1"
    And the response should contain "ssh_key2"
    And the response should contain "Proceed to SSO-SP"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
    And the response should contain "ssh_key1"
    And the response should contain "ssh_key2"

  Scenario: Stepup authentication combined with SBS 'interrupt' flow redirects to SBS then shows merged attributes on consent
    Given the SP "SSO-SP" requires Stepup LoA "http://dev.openconext.local/assurance/loa2"
    And the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "interrupt" authz flow when called
    And the sbs server will return valid attributes
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And Stepup will successfully verify a user
    Then the url should match "/functional-testing/interrupt"
    And I pass through SBS
    Then the url should match "/authentication/idp/process-sraminterrupt"
    And the response should contain "Review your information that will be shared."
    And the response should contain "test_user@test.sram.surf.nl"
    And the response should contain "ssh_key1"
    And the response should contain "ssh_key2"
    And the response should contain "Proceed to SSO-SP"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
    And the response should contain "ssh_key1"
    And the response should contain "ssh_key2"

  Scenario: If no suitable stepup can be given, sbs interrupt is not executed
    Given the SP "SSO-SP" requires Stepup LoA "http://dev.openconext.local/assurance/loa2"
    And the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "interrupt" authz flow when called
    And the sbs server will return valid attributes
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And Stepup will fail if the LoA can not be given
    Then I should see "Error - No suitable token found"
     And the url should match "/feedback/stepup-callout-unmet-loa"
     And the response status code should be 400

  Scenario: SBS flow is skipped when feature is disabled
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is disabled
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the url should match "/authentication/sp/consume-assertion"
    And the response should contain "Review your information that will be shared."
    And the response should not contain "test_user@test.sram.surf.nl"
    And the response should not contain "ssh_key1"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
    And the response should not contain "ssh_key1"

  Scenario: SBS flow is skipped when SP does not require SRAM collaboration
    Given feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "authorized" authz flow when called
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the url should match "/authentication/sp/consume-assertion"
    And the response should contain "Review your information that will be shared."
    And the response should not contain "test_user@test.sram.surf.nl"
    And the response should not contain "ssh_key1"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
    And the response should not contain "ssh_key1"

  Scenario: SBS 'authorized' flow works with trusted proxy
    Given an Identity Provider named "Trusted-IdP"
    And a Service Provider named "Proxy-SP"
    And a Service Provider named "End-SP"
    And the SP "End-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "authorized" authz flow when called
    And the sbs server will return valid attributes
    And SP "Proxy-SP" is authenticating for SP "End-SP"
    And SP "Proxy-SP" is a trusted proxy
    And SP "Proxy-SP" signs its requests
    When I log in at "Proxy-SP"
    And I select "Trusted-IdP" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "Review your information that will be shared."
    And the response should contain "test_user@test.sram.surf.nl"
    And the response should contain "ssh_key1"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/Proxy-SP/acs"
    And the response should contain "ssh_key1"
    And the response should contain "ssh_key2"

  Scenario: SBS attributes respect attribute release policy
    Given the SP "SSO-SP" requires SRAM collaboration
    And feature "eb.feature_enable_sram_interrupt" is enabled
    And the sbs server will trigger the "authorized" authz flow when called
    And the sbs server will return valid attributes
    And SP "SSO-SP" allows no attributes
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "Review your information that will be shared."
    And the response should not contain "ssh_key1"
    And the response should not contain "test_user@test.sram.surf.nl"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
    And the response should not contain "ssh_key1"
