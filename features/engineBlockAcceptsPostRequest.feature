Feature:
  Scenario: It is possible to login at Dummy SP using engineblock
    Given Dummy Sp is configured to use the "PostRequest" testcase
    When I go to "https://engine-test.demo.openconext.org/dummy/sp"
    And I press "Continue"
    Then I should see "Dummy Idp"