Feature:
  In order to support step-up authentication
  As EngineBlock
  I want to support step-up authentication by utilizing the Stepup Gateway SFO capabilities

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "SSO-IdP"
      And a Service Provider named "SSO-SP"
      And an Identity Provider named "Dummy-IdP"
      And a Service Provider named "Dummy-SP"
      And a Service Provider named "Proxy-SP"

  Scenario: Stepup authentication should be supported if set through SP configuration
    Given the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2"
    When I log in at "SSO-SP"
      And I select "SSO-IdP" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
      And Stepup will successfully verify a user
      And I give my consent
      And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Stepup authentication should be supported if set through IdP configuration mapping
      Given the IdP "SSO-IdP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2" for SP "SSO-SP"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will successfully verify a user
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Stepup authentication should be supported if set through both IdP and SP
      Given the IdP "SSO-IdP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2" for SP "SSO-SP"
        And the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa3"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will successfully verify a user
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Stepup authentication should be supported if set through PDP
      Given SP "SSO-SP" requires a policy enforcement decision
        And pdp gives a stepup obligation response for "http://vm.openconext.org/assurance/loa3"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will successfully verify a user
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Stepup authentication should be supported if set through SP AuthnRequest
      Given SP "SSO-SP" requests LoA "http://vm.openconext.org/assurance/loa3"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will successfully verify a user
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: If stepup is not used we should still go through consent
      When I log in at "Dummy-SP"
        And I select "Dummy-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/Dummy-SP/acs"

    Scenario: Stepup authentication should handle stepup if LoA level is not met but no token is allowed
      Given the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2"
        And the SP "SSO-SP" allows no Stepup token
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will fail if the LoA can not be given
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Stepup authentication should show exception when LoA level is not met
      Given the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will fail if the LoA can not be given
      Then I should see "Error - No suitable token found"
        And the url should match "/feedback/stepup-callout-unmet-loa"
        And the response status code should be 400

    Scenario: Stepup authentication should show exception when user does cancel
      Given the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will fail as the user cancelled
      Then I should see "Error - Logging in cancelled"
        And the url should match "/feedback/stepup-callout-user-cancelled"
        And the response status code should be 400

    Scenario: Stepup authentication should show exception when an unknown status is returned
      Given the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will fail on unknown invalid status
      Then I should see "Error - Unknown strong authentication failure"
        And the url should match "/feedback/stepup-callout-unknown"
        And the response status code should be 400

    # Trusted proxy logic
    Scenario: Step-up authentication should be requested for the proxied SP when using a trusted proxy setup and if configured in the proxied SP
      Given the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2"
        And SP "Proxy-SP" is authenticating for SP "SSO-SP"
        And SP "Proxy-SP" is a trusted proxy
        And SP "Proxy-SP" signs its requests
      When I log in at "Proxy-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will successfully verify a user
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/Proxy-SP/acs"

    Scenario: Stepup authentication should succeed for the proxied SP when using a trusted proxy setup, if LoA level is not met but when no token is allowed is configured in the proxied SP
      Given the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa2"
        And the SP "SSO-SP" allows no Stepup token
        And SP "Proxy-SP" is authenticating for SP "SSO-SP"
        And SP "Proxy-SP" is a trusted proxy
        And SP "Proxy-SP" signs its requests
      When I log in at "Proxy-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will fail if the LoA can not be given
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/Proxy-SP/acs"

    Scenario: Stepup authentication should fail when stepup is misconfigured
      Given the SP "SSO-SP" requires Stepup LoA "http://typo-in-config.org/assurance/laos3"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
      Then I should see "Error - An error occurred"

    Scenario: Stepup authentication should fail when LoA 3 is requested, but LoA 2 is provided
      Given the SP "SSO-SP" requires Stepup LoA "http://vm.openconext.org/assurance/loa3"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        # This should not happen, GW would normally return an unmet loa error response. But a misconfigured stepup
        # provider, or a malicious login attempt should not cause EngineBlock to fully trust the gatway but verify the
        # returned LoA level
        And Stepup will successfully verify a user with a LoA 2 token
      Then I should see "Error - An error occurred"
        And the url should match "/feedback/unknown-error"

  Scenario: Stepup authentication should be fail when insufficient LoA is provided when LoA set through SP AuthnRequest
     Given SP "SSO-SP" requests LoA "http://vm.openconext.org/assurance/loa3"
      When I log in at "SSO-SP"
       And I select "SSO-IdP" on the WAYF
       And I pass through EngineBlock
       And I pass through the IdP
       And Stepup will successfully verify a user with a LoA 2 token
      Then I should see "Error - An error occurred"
       And the url should match "/feedback/unknown-error"
