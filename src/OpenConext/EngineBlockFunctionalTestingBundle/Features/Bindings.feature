Feature:
  In order to perform Single Sign On
  As an IdP or SP
  I want to send SAML Requests  / Responses in a variety of ways

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy Idp"
      And a Service Provider named "Dummy SP"

  Scenario: EngineBlock accepts AuthnRequests using HTTP-POST binding
    Given the SP uses the HTTP POST Binding
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: EngineBlock accepts AuthnRequests using HTTP-Redirect binding
    Given the SP uses the HTTP Redirect Binding
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: EngineBlock accepts Signed AuthnRequests using HTTP-POST binding
    Given the SP uses the HTTP POST Binding
      And the SP signs its requests
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: RelayState is preserved through the complete SAML flow using HTTP-POST binding
    Given the SP uses the HTTP POST Binding
      And the SP sends RelayState "test-relay-state-post-binding"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
     Then the RelayState should be "test-relay-state-post-binding"
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: RelayState is preserved through the complete SAML flow using HTTP-Redirect binding
    Given the SP uses the HTTP Redirect Binding
      And the SP sends RelayState "test-relay-state-redirect-binding"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
     Then the RelayState should be "test-relay-state-redirect-binding"
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: RelayState with special characters is preserved through the SAML flow
    Given the SP uses the HTTP POST Binding
      And the SP sends RelayState "test+state=with&special%20chars"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
     Then the RelayState should be "test+state=with&special%20chars"
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: RelayState is preserved with signed requests using HTTP-POST binding
    Given the SP uses the HTTP POST Binding
      And the SP signs its requests
      And the SP sends RelayState "signed-request-relay-state"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
     Then the RelayState should be "signed-request-relay-state"
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: Empty RelayState is handled correctly
    Given the SP uses the HTTP POST Binding
      And the SP sends RelayState ""
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
     Then no RelayState should be present
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: No RelayState is handled correctly
    Given the SP uses the HTTP POST Binding
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
     Then no RelayState should be present
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"
