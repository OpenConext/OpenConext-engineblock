Feature:
  Scenario: It is possible to login at Dummy SP using engineblock
    Given An authn request is sent to Engineblock
    And I press "Dummy Idp"
    And I press "Continue"
    And I press "Submit"
    Then I should see "DUMMY SP"