Feature:
  Scenario: It is possible to login at Dummy SP using engineblock
    When I go to engine-test "/dummy/sp"
    And I press "Dummy Idp"
    And I press "Continue"
    And I press "Submit"
    Then I should see "DUMMY SP"