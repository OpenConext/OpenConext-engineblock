Feature: Support for multiple languages.

  Scenario: User starts login process
    Given I am on "Shibboleth.sso/Login"
    Then I should see "Login via your institution"

  Scenario: User selects NL as language
    Given I am on "Shibboleth.sso/Login"
      And I follow "NL"
     Then I should see "Login via je eigen instelling"
