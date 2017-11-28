@WIP
Feature:
  In order to offer a predictable API to SPs and IdPs
  As EngineBlock
  I want to send them the correct AuthnRequests and Responses

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "SSO-IdP"
      And a Service Provider named "SSO-SP"

  Scenario: IdPs are allowed to create NameIDs
    When I log in at "SSO-SP"
     And I pass through EngineBlock
     And I pass through the IdP
    Then the AuthnRequest to submit should match xpath '/samlp:AuthnRequest/samlp:NameIDPolicy[@AllowCreate="true" or @AllowCreate="1"]'
