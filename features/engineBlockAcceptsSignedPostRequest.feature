Feature:
  Scenario: EngineBlock accepts Signed AuthnRequests using HTTP-POST binding
    Given Dummy Sp is configured to use the "SignedPostRequest" testcase
    When I go to "https://engine-test.demo.openconext.org/dummy/sp"
    And I press "Continue"
    Then I should see "Dummy Idp"