Feature:
  In order to support passive authentications
  As an SP
  The IsPassive implementation in EB should function correctly

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "Dummy IdP"
    And a Service Provider named "Dummy SP"

  Scenario: A passive AuthnRequest is handled without issue
    Given SP "Dummy SP" is configured to generate a passive AuthnRequest
    When I log in at "Dummy SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the response should match xpath '/samlp:Response/samlp:Status/samlp:StatusCode[@Value="urn:oasis:names:tc:SAML:2.0:status:Success"]'

  Scenario: A NoPassive response is forwarded to the SP
    Given SP "Dummy SP" is configured to generate a passive AuthnRequest
    And the IdP is configured to always return Responses with StatusCode Responder/NoPassive
    When I log in at "Dummy SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I pass through EngineBlock
    Then the response should match xpath '/samlp:Response/samlp:Status/samlp:StatusCode[@Value="urn:oasis:names:tc:SAML:2.0:status:Responder"]/samlp:StatusCode[@Value="urn:oasis:names:tc:SAML:2.0:status:NoPassive"]'
