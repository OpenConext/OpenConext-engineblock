Feature:
  Scenario: EngineBlock fails on Idp giving an invalid name id policy status code
    Given An authn request is sent to Engineblock
    And I press "Dummy Idp"
    And I press "Continue"
    And I press "Submit"
    Then I should see "DUMMY SP"