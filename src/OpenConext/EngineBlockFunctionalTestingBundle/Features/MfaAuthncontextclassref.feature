Feature:
  In order to support MFA
  As EngineBlock
  I want to support configurable authncontextclassrefs for certain IdP SP combinations

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "SSO-IdP"
      And a Service Provider named "SSO-SP"

    Scenario: The configured authncontextclassref should be set if configured with the IdP configuration mapping
      Given the IdP "SSO-IdP" is configured for authncontextclassref "http://schemas.microsoft.com/claims/multipleauthn" for SP "SSO-SP"
      When I log in at "SSO-SP"
        And I pass through EngineBlock
      Then the url should match "functional-testing/SSO-IdP/sso"
        And the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:RequestedAuthnContext/saml:AuthnContextClassRef[text()="http://schemas.microsoft.com/claims/multipleauthn"]'

    Scenario: The configured authncontextclassref should not be set if not configured with the IdP configuration mapping
      When I log in at "SSO-SP"
        And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
      And the response should not contain "http://schemas.microsoft.com/claims/multipleauthn"

  Scenario: The configured authncontextclassref should be set if configured with the IdP configuration mapping also for unsolicited logins
    Given the IdP "SSO-IdP" is configured for authncontextclassref "http://schemas.microsoft.com/claims/multipleauthn" for SP "SSO-SP"
    When An IdP initiated Single Sign on for SP "SSO-SP" is triggered by IdP "SSO-IdP"
      And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
    And the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:RequestedAuthnContext/saml:AuthnContextClassRef[text()="http://schemas.microsoft.com/claims/multipleauthn"]'
