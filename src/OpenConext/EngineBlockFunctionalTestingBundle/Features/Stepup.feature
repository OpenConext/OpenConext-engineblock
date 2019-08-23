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

  Scenario: Stepup authentication should be supported if set through SP configuration
    Given the SP "SSO-SP" requires Stepup LoA "http://test.openconext.nl/assurance/loa2"
    When I log in at "SSO-SP"
      And I select "SSO-IdP" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
      And Stepup will successfully verify a user
      And I give my consent
      And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Stepup authentication should be supported if set trough IdP configuration mapping
      Given the IdP "SSO-IdP" requires Stepup LoA "http://test.openconext.nl/assurance/loa2" for SP "SSO-SP"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will successfully verify a user
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Stepup authentication should throw exception if set trough both IdP and SP
      Given the IdP "SSO-IdP" requires Stepup LoA "http://test.openconext.nl/assurance/loa2" for SP "SSO-SP"
        And the SP "SSO-SP" requires Stepup LoA "http://test.openconext.nl/assurance/loa3"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
      Then I should see "Error - An error occurred"
        And the url should match "/feedback/unknown-error"

    Scenario: If stepup is not used we should still go trough consent
      When I log in at "Dummy-SP"
        And I select "Dummy-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/Dummy-SP/acs"

    Scenario: Stepup authentication should handle stepup if LoA level is not met but no token is allowed
      Given the SP "SSO-SP" requires Stepup LoA "http://test.openconext.nl/assurance/loa2"
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
      Given the SP "SSO-SP" requires Stepup LoA "http://test.openconext.nl/assurance/loa2"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will fail if the LoA can not be given
      Then I should see "Error - No suitable token found"
        And the url should match "/feedback/stepup-callout-unmet-loa"

    Scenario: Stepup authentication should show exception when user does cancel
      Given the SP "SSO-SP" requires Stepup LoA "http://test.openconext.nl/assurance/loa2"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will fail as the user cancelled
      Then I should see "Error - Logging in cancelled"
        And the url should match "/feedback/stepup-callout-user-cancelled"

    Scenario: Stepup authentication should show exception when an unknown status is returned
      Given the SP "SSO-SP" requires Stepup LoA "http://test.openconext.nl/assurance/loa2"
      When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And Stepup will fail on unknown invalid status
      Then I should see "Error - Unknown strong authentication failure"
        And the url should match "/feedback/stepup-callout-unknown"
