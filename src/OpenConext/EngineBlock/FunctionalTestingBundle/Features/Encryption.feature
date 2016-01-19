Feature:
  In order to protect my Responses from prying eyes
  As an IdP
  I want to send Encrypted SAML Responses

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy Idp"
      And a Service Provider named "Dummy SP"

  Scenario: EngineBlock accepts RSA Encrypted Responses
    Given the SP uses the HTTP POST Binding
      And the IdP encrypts it's assertions with the public key in "/etc/openconext/engineblock.crt"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: EngineBlock rejects invalid RSA Encrypted Responses
    Given the SP uses the HTTP POST Binding
      And the IdP encrypts it's assertions with the public key in "src/OpenConext/EngineBlock/FunctionalTestingBundle/Resources/keys/rolled-over.crt"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Invalid Identity Provider response"

  Scenario: EngineBlock rejects Shared Key Encrypted Responses
    Given the SP uses the HTTP POST Binding
      And the IdP encrypts it's assertions with the shared key "IUVupkwUmm1hO6P2crD2WM1aQUmyomBA"
     When I log in at "Dummy SP"
      And I pass through the SP
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Invalid Identity Provider response"
