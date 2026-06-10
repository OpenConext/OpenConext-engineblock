Feature:
  In order to support MFA
  As EngineBlock
  I want to support configurable AuthnContextClassRefs for certain IdP SP combinations

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "SSO-IdP"
    And a Service Provider named "SSO-SP"
    And a Service Provider named "Trusted SP"

  Scenario: The configured authn method should be set as AuthnContextClassRef if configured with the IdP configuration mapping
    Given the IdP "SSO-IdP" is configured for MFA authn method "http://schemas.microsoft.com/claims/multipleauthn" for SP "SSO-SP"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
    And the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:RequestedAuthnContext/saml:AuthnContextClassRef[text()="http://schemas.microsoft.com/claims/multipleauthn"]'

  Scenario: The configured authn method should be set as AuthnContextClassRef if configured with the IdP configuration mapping for Trusted Proxy
    Given the IdP "SSO-IdP" is configured for MFA authn method "http://schemas.microsoft.com/claims/multipleauthn" for SP "SSO-SP"
    And SP "Trusted SP" is a trusted proxy
    And SP "Trusted SP" signs its requests
    And  SP "Trusted SP" is authenticating for SP "SSO-SP"
    When I log in at "Trusted SP"
    And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
    And the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:RequestedAuthnContext/saml:AuthnContextClassRef[text()="http://schemas.microsoft.com/claims/multipleauthn"]'

  Scenario: The configured authn method should not be set as AuthnContextClassRef if not configured in the IdP configuration mapping
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
    And the response should not contain "http://schemas.microsoft.com/claims/multipleauthn"

  Scenario: The configured authn method should also be set for unsolicited logins if configured in the IdP configuration mapping
    Given the IdP "SSO-IdP" is configured for MFA authn method "http://schemas.microsoft.com/claims/multipleauthn" for SP "SSO-SP"
    When An IdP initiated Single Sign on for SP "SSO-SP" is triggered by IdP "SSO-IdP"
    And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
    And the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:RequestedAuthnContext/saml:AuthnContextClassRef[text()="http://schemas.microsoft.com/claims/multipleauthn"]'

  Scenario: A login should succeed if the configured authn method is set as AuthnContextClassRef in the IdP response
    Given the IdP "SSO-IdP" is configured for MFA authn method "http://schemas.microsoft.com/claims/multipleauthn" for SP "SSO-SP"
    And the IdP "SSO-IdP" sends AuthnContextClassRef with value "http://schemas.microsoft.com/claims/multipleauthn"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"

  Scenario: A login should succeed if the configured authn method is set as one of the values in the http://schemas.microsoft.com/claims/authnmethodsreferences attribute in the IdP response
    Given the IdP "SSO-IdP" is configured for MFA authn method "http://schemas.microsoft.com/claims/multipleauthn" for SP "SSO-SP"
    And the IdP "SSO-IdP" sends attribute "http://schemas.microsoft.com/claims/authnmethodsreferences" with values "http://schemas.microsoft.com/claims/multipleauthn" and xsi:type is "xs:string"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"

  Scenario: A login should fail if the configured authn method is not in the IdP response as AuthnContextClassRef or as a value in the http://schemas.microsoft.com/claims/authnmethodsreferences attribute
    Given the IdP "SSO-IdP" is configured for MFA authn method "http://schemas.microsoft.com/claims/multipleauthn" for SP "SSO-SP"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then I should see "Error - Multi factor authentication failed"
    And the url should match "/authentication/feedback/invalid-mfa-authn-context-class-ref"

  Scenario: The SP provided authn method should be set as AuthnContextClassRef if SP configured with transparent_authn_context
    Given the IdP "SSO-IdP" is configured for MFA authn method "transparent_authn_context" for SP "SSO-SP"
    And the SP "SSO-SP" sends AuthnContextClassRef with value "http://my-very-own-context.example.com/level9"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
    And the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:RequestedAuthnContext/saml:AuthnContextClassRef[text()="http://my-very-own-context.example.com/level9"]'
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"

  Scenario: The SP provided authn method should NOT be set as AuthnContextClassRef if SP configured is not with transparent_authn_context
    Given the IdP "SSO-IdP" is configured for MFA authn method "not_configured_transparent_authn_context" for SP "SSO-SP"
    And the SP "SSO-SP" sends AuthnContextClassRef with value "http://my-very-own-context.example.com/level9"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
    And the response should not contain "http://my-very-own-context.example.com/level9"

  Scenario: While using transparent_authn_context AuthnFailed response is also passed transparently
    Given the IdP "SSO-IdP" is configured for MFA authn method "transparent_authn_context" for SP "SSO-SP"
    And the IdP is configured to always return Responses with StatusCode Responder/AuthnFailed
    And the SP "SSO-SP" sends AuthnContextClassRef with value "http://sp-specific-loa.org/super-secure-second-factor-authn"
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
    And the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:RequestedAuthnContext/saml:AuthnContextClassRef[text()="http://sp-specific-loa.org/super-secure-second-factor-authn"]'
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
   Then the url should match "/functional-testing/SSO-SP/acs"
    And the response should contain "urn:oasis:names:tc:SAML:2.0:status:Responder"
    And the response should contain "urn:oasis:names:tc:SAML:2.0:status:AuthnFailed"

  Scenario: A login should succeed if the we don't have a RequestedAuthnContext but a default authn class ref is configured
    Given the IdP "SSO-IdP" has a default RequestedAuthnContext configured as "http://default-context.example.com/authn"
    And the SP "SSO-SP" sends no AuthnContextClassRef
    When I log in at "SSO-SP"
    And I pass through EngineBlock
    Then the url should match "functional-testing/SSO-IdP/sso"
    And the response should contain "http://default-context.example.com/authn"
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the url should match "/functional-testing/SSO-SP/acs"
