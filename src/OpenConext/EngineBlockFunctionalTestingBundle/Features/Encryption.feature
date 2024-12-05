Feature:
  In order to protect my Responses from prying eyes
  As an IdP
  I want to send Encrypted SAML Responses

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy Idp"
      And a Service Provider named "Dummy SP"

  Scenario: EngineBlock accepts RSA Encrypted Responses
    Given the SP uses the HTTP POST Binding
      And feature "eb.encrypted_assertions" is enabled
      And the IdP encrypts its assertions with the public key in "/config/engine/engineblock.crt"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: EngineBlock rejects invalid RSA Encrypted Responses
    Given the SP uses the HTTP POST Binding
      And feature "eb.encrypted_assertions" is enabled
      And feature "eb.encrypted_assertions_require_outer_signature" is enabled
      And the IdP encrypts its assertions with the public key in "src/OpenConext/EngineBlockFunctionalTestingBundle/Resources/keys/rolled-over.crt"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Invalid organisation SAML response"

  Scenario: EngineBlock rejects Shared Key Encrypted Responses
    Given the SP uses the HTTP POST Binding
      And feature "eb.encrypted_assertions" is enabled
      And the IdP encrypts its assertions with the shared key "IUVupkwUmm1hO6P2crD2WM1aQUmyomBA"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Invalid organisation SAML response"

  Scenario: EngineBlock rejects encrypted responses if the feature "eb.encrypted_assertions" is not enabled
    Given the SP uses the HTTP POST Binding
      And feature "eb.encrypted_assertions" is disabled
      And the IdP encrypts its assertions with the public key in "tests/resources/key/engineblock.crt"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
     Then the url should match "authentication/feedback/received-invalid-response"
      And I should see "Invalid organisation SAML response"

  Scenario: EngineBlock rejects encrypted responses without outer signature if the feature "eb.encrypted_assertions_require_outer_signatures" is enabled
    Given the SP uses the HTTP POST Binding
      And feature "eb.encrypted_assertions" is enabled
      And feature "eb.encrypted_assertions_require_outer_signature" is enabled
      And the IdP encrypts its assertions with the public key in "tests/resources/key/engineblock.crt"
      And the IdP does not sign its responses
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
     Then the url should match "authentication/feedback/received-invalid-response"
      And I should see "Invalid organisation SAML response"

  # This scenario is currently not supported by EngineBlock,
  # see https://www.pivotaltracker.com/story/show/155703943
  @SKIP
  Scenario: EngineBlock accepts encrypted responses without an outer signature if the feature "eb.encrypted_assertions_require_outer_signatures" is disabled
    Given the SP uses the HTTP POST Binding
      And feature "eb.encrypted_assertions" is enabled
      And feature "eb.encrypted_assertions_require_outer_signature" is disabled
     When I log in at "Dummy SP"
      And the IdP encrypts its assertions with the public key in "tests/resources/key/engineblock.crt"
      And the IdP does not sign its responses
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: EngineBlock supports not signed responses
    Given the SP uses the HTTP POST Binding
    And SP "Dummy SP" does not require a signed response
    When I log in at "Dummy SP"
    And I pass through the SP
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the response should not match xpath '//samlp:Response/ds:Signature/ds:SignedInfo/ds:SignatureMethod'

  Scenario: EngineBlock supports signed responses
    Given the SP uses the HTTP POST Binding
    And SP "Dummy SP" requires a signed response
    When I log in at "Dummy SP"
    And I pass through the SP
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the response should match xpath '//samlp:Response/ds:Signature/ds:SignedInfo/ds:SignatureMethod'
