Feature:
  In order to support 2FA we need to support gateway interactions
  As EngineBlock
  I want to support 2FA by utilizing the Stepup gateway SFO capabilities

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "SSO-IdP"
      And an Identity Provider named "SSO-Foobar"
      And a Service Provider named "SSO-SP"
      And a Service Provider named "SSO-Foobar"

  Scenario: Sfo should be supported
    When SFO is used
     And SFO will successfully verify a user
     And I log in at "SSO-SP"
     And I select "SSO-IdP" on the WAYF
     And I pass through EngineBlock
    Then the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:NameIDPolicy[@AllowCreate="true" or @AllowCreate="1"]'
