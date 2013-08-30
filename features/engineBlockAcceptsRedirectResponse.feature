Feature:
  Scenario: EngineBlock accepts AuthnRequests using HTTP-Redirect binding
    Given Dummy Idp is configured to use the "RedirectResponse" testcase
    When I go to engine-test "/dummy/sp"
    And I press "Dummy Idp"
    Then the url should match "consume-assertion"