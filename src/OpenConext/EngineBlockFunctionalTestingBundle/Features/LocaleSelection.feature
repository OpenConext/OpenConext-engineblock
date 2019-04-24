Feature:
  As a user,
  I want EB to display the interface in the language that my browser sends,
  so I can understand the interface better.
  Where desired, I want to override the language with the EN/NL buttons in the interface.

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
    And an Identity Provider named "First IdP"
    And an Identity Provider named "Second IdP"
    And my browser is configured to accept language "nl-NL"

  Scenario: a user makes their first visit and doesn't have a locale cookie
    When I go to Engineblock URL "/authentication/sp/debug"
    Then a lang cookie should be set with value "nl"
    And I should see "Gebruiksvoorwaarden"

  Scenario: a user requests an unsupported locale, so EngineBlock will fallback to the default locale
    Given my browser is configured to accept language "de-DE"
    When I go to Engineblock URL "/authentication/sp/debug"
    Then a lang cookie should be set with value "en"
    And I should see "Terms of Service"

  Scenario: a user makes a recurring visit
    Given I have a locale cookie containing "nl"
    When I go to Engineblock URL "/authentication/sp/debug"
    Then a lang cookie should be set with value "nl"
    And I should see "Gebruiksvoorwaarden"

  Scenario: a user changes their locale
    Given I have a locale cookie containing "nl"
    When I go to Engineblock URL "/authentication/sp/debug?lang=en"
    Then a lang cookie should be set with value "en"
    And I should see "Terms of Service"
