Feature: Azure / EntraID domain hint
  In order to skip the Microsoft Azure account picker for users whose realm is known
  As an IdP operator
  I want to configure coin:azure_domain_hint on an IdP so that EngineBlock appends whr=<domain>
  to the HTTP-Redirect AuthnRequest URL it sends to that IdP

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Azure IdP"
      And a Service Provider named "Dummy SP"

  Scenario: EngineBlock appends whr query parameter when coin:azure_domain_hint is configured
    Given IDP "Azure IdP" has Azure domain hint "hartingcollege.nl"
     When I log in at "Dummy SP"
     Then the full url should match "whr=hartingcollege\.nl"
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: EngineBlock does not append whr query parameter when coin:azure_domain_hint is not configured
    Given IDP "Azure IdP" prefers HTTP Redirect binding
     When I log in at "Dummy SP"
     Then the url should not match "whr="
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"
