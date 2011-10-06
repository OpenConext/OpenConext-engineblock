Feature: Searching for IdP
  In order to quickly select my Identity provider
  As an end-user
  I want to search for my Identity Provider

  Scenario: User searches for IdP/Institution
   Given I am on "Shibboleth.sso/Login"
     And I fill in "searchBox" with "Hogeschool"
    Then print last response
