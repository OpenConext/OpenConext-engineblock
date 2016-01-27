@replay
Feature:
  Background:
    Given an EngineBlock instance configured with JSON data
    And an Identity Provider named "Replay Idp"
    And a Service Provider named "Replay SP"

  Scenario: Replay login requests
    Given the SP is configured to generate a AuthnRequest like the one at "fixtures/replay/sp.request.log"
      And the SP does not require consent
      And the SP may run in transparent mode, if indicated in "fixtures/replay/session.log"
      And the IdP is configured to return a Response like the one at "fixtures/replay/idp.response.log"
      And the SP may only access the IdP
      And EngineBlock is expected to send a AuthnRequest like the one at "fixtures/replay/eb.request.log"
      And EngineBlock is expected to send a Response like the one at "fixtures/replay/eb.response.log"
      And I print the configured ids
     When I trigger the login (either at "Replay SP" or unsolicited at EB)
      And print last response
      And I follow the EB debug screen to the IdP
      And print last response
     Then the request should be compared with the one at "fixtures/replay/eb.request.log"
      And I press "GO"
      And print last response
      And I press "Submit"
      And print last response
     Then the response should be compared with the one at "fixtures/replay/eb.response.log"