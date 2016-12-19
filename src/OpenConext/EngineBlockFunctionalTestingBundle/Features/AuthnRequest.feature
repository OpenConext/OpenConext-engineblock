@WIP
Feature:
  In order to maintain compatibility with certain IdPs
  As Engineblock
  I want to send correct AuthnRequests

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy-IdP"
      And a Service Provider named "Dummy-SP"

  Scenario: The AuthnRequest contains the correct attributes
    When I log in at "Dummy-SP"
     And I pass through EngineBlock
     And I pass through the IdP
    Then the AuthnRequest should match xpath '/samlp:AuthnRequest/samlp:NameIDPolicy[@AllowCreate="true" or @AllowCreate="1"]'
