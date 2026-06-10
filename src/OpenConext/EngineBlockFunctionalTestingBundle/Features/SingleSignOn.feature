Feature:
  In order to offer a predictable API to SPs and IdPs
  As EngineBlock
  I want to send them the correct AuthnRequests and Responses

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "SSO-IdP"
      And an Identity Provider named "SSO-Foobar"
      And a Service Provider named "SSO-SP"
      And a Service Provider named "SSO-Foobar"

  Scenario: IdPs are allowed to create NameIDs
    When I log in at "SSO-SP"
     And I select "SSO-IdP" on the WAYF
     And I pass through EngineBlock
    Then the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:NameIDPolicy[@AllowCreate="true" or @AllowCreate="1"]'

  Scenario: IdPs and SPs can share EntityID
    When I log in at "SSO-Foobar"
    Then I should see "SSO-Foobar"
     And I should see "SSO-IdP"

  Scenario: EngineBlock should not add the return parameter to the process form when no processedAssertionConsumerService is available
    When I log in at "SSO-SP"
     And I select "SSO-IdP" on the WAYF
    Then The process form should have the "SAMLRequest" field
     And The process form should not have the "return" field
     And The process form should not have the "RelayState" field
