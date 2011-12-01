Feature: Support for multiple languages
  In order to pick an Identity Provider
  As an end-user
  I want to see the WAYF in my language

  Scenario: User starts login process
    Given I am on "Shibboleth.sso/Login"
     Then I should see "Login via your institution"

  Scenario: User selects NL as language
    Given I am on "Shibboleth.sso/Login"
      And I follow "NL"
     Then I should see "Login via je eigen instelling"
