Feature:
  As a user,
  I want EB to display the interface in the language that my browser sends,
  so I can understand the interface better.
  Where desired, I want to override the language with the EN/NL buttons in the interface.

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
    And my browser is configured to prefer "nl-NL"

  Scenario: a user makes their first visit and doesn't have a locale cookie
    Given I don't have a cookie with a locale preference set
    When I go to Engineblock URL "/authentication/sp/debug"
    Then a cookie should be set with locale "nl"
    And I should see "Gebruiksvoorwaarden"

  Scenario: a user makes a recurring visit
    Given I have a locale cookie containing "nl"
    When I go to Engineblock URL "/authentication/sp/debug"
    Then a cookie should be set with locale "nl"
    And I should see "Gebruiksvoorwaarden"

  Scenario: a user changes their locale
    Given I have a locale cookie containing "nl"
    When I go to Engineblock URL "/authentication/sp/debug?lang=en"
    Then a cookie should be set with locale "en"
    And I should see "Terms of Service"
