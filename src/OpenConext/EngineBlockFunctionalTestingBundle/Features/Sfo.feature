Feature:
  In order to support 2FA we need to support gateway interactions
  As EngineBlock
  I want to support 2FA by utilizing the Stepup gateway SFO capabilities

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "SSO-IdP"
      And a Service Provider named "SSO-SP"
      And an Identity Provider named "Dummy-IdP"
      And a Service Provider named "Dummy-SP"

  Scenario: Sfo should be supported if set through sp configuration
    Given SFO is used
      And the SP "SSO-SP" requires SFO loa "http://test.openconext.nl/assurance/loa2"
    When I log in at "SSO-SP"
      And I select "SSO-IdP" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
      And I pass through EngineBlock
      And SFO will successfully verify a user
      And I give my consent
      And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Sfo should be supported if set trough idp configuration mapping
        Given SFO is used
        And the IdP "SSO-IdP" requires SFO loa "http://test.openconext.nl/assurance/loa2" for SP "SSO-SP"
        When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And I pass through EngineBlock
        And SFO will successfully verify a user
        And I give my consent
        And I pass through EngineBlock
        Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Sfo should throw exception if set trough both IdP and SP
        Given SFO is used
        And the IdP "SSO-IdP" requires SFO loa "http://test.openconext.nl/assurance/loa2" for SP "SSO-SP"
        And the SP "SSO-SP" requires SFO loa "http://test.openconext.nl/assurance/loa3"
        When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        Then I should see "Error - An error occurred"
        And the url should match "/feedback/unknown-error"

    Scenario: If sfo is not used we should still go trough consent
      When I log in at "Dummy-SP"
        And I select "Dummy-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/Dummy-SP/acs"

    Scenario: Sfo should handle sfo if loa level is not met but no token is allowed
        Given SFO is used
        And the SP "SSO-SP" requires SFO loa "http://test.openconext.nl/assurance/loa2"
        And the SP "SSO-SP" allows no SFO token
        When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And I pass through EngineBlock
        And SFO will fail if the loa can not be given
        And I give my consent
        And I pass through EngineBlock
        Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: Sfo should show exception when loa level is not met
        Given SFO is used
        And the SP "SSO-SP" requires SFO loa "http://test.openconext.nl/assurance/loa2"
        When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And I pass through EngineBlock
        And SFO will fail if the loa can not be given
        Then I should see "Error - No suitable token found"
        And the url should match "/feedback/sfo-callout-unmet-loa"

    Scenario: Sfo should show exception when user does cancel
        Given SFO is used
        And the SP "SSO-SP" requires SFO loa "http://test.openconext.nl/assurance/loa2"
        When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And I pass through EngineBlock
        And SFO will fail as the user cancelled
        Then I should see "Error - Logging in cancelled"
        And the url should match "/feedback/sfo-callout-user-cancelled"

    Scenario: Sfo should show exception when an unknown status is returned
        Given SFO is used
        And the SP "SSO-SP" requires SFO loa "http://test.openconext.nl/assurance/loa2"
        When I log in at "SSO-SP"
        And I select "SSO-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And I pass through EngineBlock
        And SFO will fail on unknown invalid status
        Then I should see "Error - Unknown strong authentication failure"
        And the url should match "/feedback/sfo-callout-unknown"
