Feature:
  In order to determine the response status
  As a SAML consumer
  The correct status codes and sub status codes should be shown

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
     And no registered SPs
     And no registered Idps
     And an Identity Provider named "Dummy Idp"
     And a Service Provider named "Dummy SP"

  Scenario: Proxying exceeds the allowed ProxyCount in the AuthnRequest
    Given SP "Dummy SP" is configured to generate a AuthnRequest with a ProxyCount of 0
     When I log in at "Dummy SP"
      And I pass through EngineBlock
     Then the response should match xpath '/samlp:Response/samlp:Status/samlp:StatusCode[@Value="urn:oasis:names:tc:SAML:2.0:status:Responder"]/samlp:StatusCode[@Value="urn:oasis:names:tc:SAML:2.0:status:ProxyCountExceeded"]'

  Scenario: A passive AuthnRequest has been received, but it can be handled by more than one IdP
    Given an Identity Provider named "Extra IdP"
     And SP "Dummy SP" is configured to generate a passive AuthnRequest
     When I log in at "Dummy SP"
      And I give my consent
      And I pass through EngineBlock
     Then the response should match xpath '/samlp:Response/samlp:Status/samlp:StatusCode[@Value="urn:oasis:names:tc:SAML:2.0:status:Responder"]/samlp:StatusCode[@Value="urn:oasis:names:tc:SAML:2.0:status:NoPassive"]'
