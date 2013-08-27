Feature:
  In order to explain my login problem's to the helpdesk
  As a user
  I need to see useful error information when something goes wrong

    Scenario: Engineblock shows useful status code and message when Idp returns an error code
    Given Dummy Idp is configured to use the "ErrorStatusCode" testcase
    When I go to "https://engine-test.demo.openconext.org/dummy/sp"
    And I press "Dummy Idp"
    And I press "Continue"
    Then I should see "Idp error"
    And I should see "Status Code: urn:oasis:names:tc:SAML:2.0:status:InvalidNameIDPolicy"
    And I should see "Status Message: NameIdPolicy is invalid"