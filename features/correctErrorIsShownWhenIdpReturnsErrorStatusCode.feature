Feature:
  Scenario: Engineblock shows useful message when Idp returns an error code
    Given Dummy Idp is configured to give the "InvalidNameIdPolicy" response
    When I go to "https://engine-test.demo.openconext.org/dummy-sp"
    And I press "Dummy Idp"
    And I press "Continue"
    Then I should see "Idp has an error"