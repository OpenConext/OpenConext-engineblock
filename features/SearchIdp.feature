Feature: Searching for IdP

  Scenario: User searches for IdP/Institution
   Given I am on "Shibboleth.sso/Login"
     And I fill in "searchBox" with "Hogeschool"
    Then print last response
