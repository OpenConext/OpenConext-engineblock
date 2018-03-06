Feature:
  In order to explain my login problem's to the helpdesk
  As a user
  I need to see useful error information when something goes wrong

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy Idp"
      And a Service Provider named "Dummy SP"
      And a Service Provider named "Unconnected SP"
      And an unregistered Service Provider named "Unregistered SP"
      And SP "Unconnected SP" is not connected to IdP "Dummy Idp"

  Scenario: I log in at my Identity Provider, but something goes wrong and it returns an error response.
    Given the IdP is configured to always return Responses with StatusCode Requester/InvalidNameIDPolicy
      And the IdP is configured to always return Responses with StatusMessage "NameIdPolicy is invalid"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "InvalidNameIDPolicy"
      And I should see "NameIdPolicy is invalid"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"

    Scenario: I log in at my Identity Provider, but the IdP gives a message that I don't have access.
    Given the IdP is configured to always return Responses with StatusCode Responder/RequestDenied
      And the IdP is configured to always return Responses with StatusMessage "Invalid IP range"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "RequestDenied"
      And I should see "Invalid IP range"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"

    Scenario: I log in at my Identity Provider, but I don't have access.
    Given the IdP is configured to always return Responses with StatusCode Responder/RequestDenied
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "RequestDenied"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"

    Scenario: I log in at my Identity Provider, that does not send assertions, but they give a message that I don't have access.
    Given the IdP is configured to always return Responses with StatusCode Responder/RequestDenied
      And the IdP is configured to always return Responses with StatusMessage "Invalid IP range"
      And the IdP is configured to not send an Assertion
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "RequestDenied"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"

    Scenario: I log in at my Identity Provider, that does not send assertions, but I don't have access.
    Given the IdP is configured to always return Responses with StatusCode Responder/RequestDenied
      And the IdP is configured to not send an Assertion
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Identity Provider error"
      And I should see "RequestDenied"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"

  Scenario: I log in at my Identity Provider, but it has changed (private/public) keys without notifying OpenConext
    Given the IdP uses the private key at "src/OpenConext/EngineBlockFunctionalTestingBundle/Resources/keys/rolled-over.key"
      And the IdP uses the certificate at "src/OpenConext/EngineBlockFunctionalTestingBundle//Resources/keys/rolled-over.crt"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Invalid Identity Provider response"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"

  Scenario: I want to log on, but this Service Provider may not access any Identity Providers
    When I log in at "Unconnected SP"
    Then I should see "No Identity Providers found"
     And I should see "Timestamp:"
     And I should see "Unique Request Id:"
     And I should see "User Agent:"
     And I should see "IP Address:"
     And I should see "Service Provider:"
     And I should not see "Identity Provider:"

  Scenario: I want to log on but this Service Provider is not yet registered at OpenConext
    When I log in at "Unregistered SP"
    Then I should see "Unknown service"
     And I should see "Timestamp:"
     And I should see "Unique Request Id:"
     And I should see "User Agent:"
     And I should see "IP Address:"
     And I should see "EntityID:"
     And I should not see "Identity Provider:"

  Scenario: An Identity Provider misrepresents its entityId and is thus not recognized by EB
    Given the IdP thinks its EntityID is "https://wrong.example.edu/metadata"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Unknown service"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"
      And I should see "https://wrong.example.edu/metadata"

  Scenario: An Identity Provider tries to send a response over HTTP-Redirect, violating the spec
    Given the IdP uses the HTTP Redirect Binding
     When I log in at "Dummy SP"
      And I pass through EngineBlock
     Then I should see "Invalid ACS Binding Type"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"

  Scenario: An Identity Provider sends a response without a SHO
    Given the IdP does not send the attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Missing required fields"
      And I should see "UID"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"

  Scenario: An Identity Provider sends a response without a uid
    Given the IdP does not send the attribute named "urn:mace:dir:attribute-def:uid"
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Missing required fields"
      And I should see "schacHomeOrganization"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"
      And I should see "Identity Provider:"

  Scenario: An SP sends a AuthnRequest transparently for an IdP that doesn't exist
     When I log in at SP "Dummy SP" which attempts to preselect nonexistent IdP "DoesNotExist"
     Then the url should match "/authentication/feedback/unknown-preselected-idp"
      And I should see "No connection between organisation and service"
      And I should see "Timestamp:"
      And I should see "Unique Request Id:"
      And I should see "User Agent:"
      And I should see "IP Address:"
      And I should see "Service Provider:"

  Scenario: The session has been lost after passing through EngineBlock
    When I log in at "Dummy SP"
     And I pass through EngineBlock
     And I lose my session
     And I pass through the IdP
     And I should see "your session was lost"

  Scenario: The session has been lost after logging in at the SP
    When I log in at "Dummy SP"
     And I lose my session
     And I pass through EngineBlock
     And I pass through the IdP
     And I should see "your session was lost"

#
#  Scenario: I try an unsolicited login (at EB) but mess up by not specifying a location
#  Scenario: I try an unsolicited login (at EB) but mess up by not specifying a binding
#  Scenario: I try an unsolicited login (at EB) but mess up by not specifying an invalid index
#
#
#  Scenario: I don't give consent to release my attributes to a Service Provider
#
#  Scenario: I visit the EngineBlock SSO location without a SAMLRequest
#  Scenario: I visit the EngineBlock ACS location without a SAMLResponse
#  Scenario: I visit the EngineBlock SSO location with a bad SAMLRequest
#  Scenario: I visit the EngineBlock ACS location with a bad SAMLResponse
#
#  Scenario: I lose my 'main' session cookie.
#
#  Scenario: An attribute manipulation determines that a user may not continue
#
#  Scenario: An Identity Provider dates its assertions in the future.
#  Scenario: I want to log in to a service but am not a member of the appropriate VO
