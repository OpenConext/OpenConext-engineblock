Feature:
  Scenario: EngineBlock accepts AuthnRequests using HTTP-POST binding
    Given Dummy Sp is configured to use the "PostRequest" testcase
    When I go to engine-test "/dummy/sp"
    And I press "Continue"
    Then I should see "Dummy Idp"