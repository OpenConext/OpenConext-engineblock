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

  Scenario: Sfo should be supported
    Given SFO is used
      And SFO will successfully verify a user
      And the SP "SSO-SP" requires SFO loa "http://test.openconext.nl/assurance/loa2"
    Then I log in at "SSO-SP"
      And I select "SSO-IdP" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
      And I pass through EngineBlock
      And I authenticate with SFO
      And I give my consent
      And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"

    Scenario: If sfo is not used we should still go trough consent
      Then I log in at "Dummy-SP"
        And I select "Dummy-IdP" on the WAYF
        And I pass through EngineBlock
        And I pass through the IdP
        And I give my consent
        And I pass through EngineBlock
      Then the url should match "/functional-testing/Dummy-SP/acs"
