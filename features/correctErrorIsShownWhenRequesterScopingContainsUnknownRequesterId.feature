Feature:
  Scenario: Engineblock shows useful message when Request scoping contains unknown requester id
    Given Dummy Sp is configured to use the "UnknownScopingRequester" testcase
    When I go to "https://engine-test.demo.openconext.org/dummy/sp"
    Then print last response
    And I press "Dummy Idp"
    And I press "Continue"
    And I press "Submit"
    Then print last response
    Then I should see "Unknown RequesterID "
    And I should see "EntityID https://profile-test.surfconext.nl/simplesaml/module.php/saml/sp/metadata.php/default-sp"
    And I should see "DestinationID https://engine.surfconext.nl/authentication/idp/single-sign-on"
    And I should see "RequesterID <The requestorID we could not find>"