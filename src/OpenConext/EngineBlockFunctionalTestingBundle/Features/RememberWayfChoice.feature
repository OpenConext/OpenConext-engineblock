Feature: Per-SP remembered WAYF choice
  In order to not have to pick my IdP every time I log in to the same SP
  As a user
  I want EngineBlock to remember my previous IdP choice for that SP

  # This is the per-SP remember-choice feature (feature_enable_wayf_remember_choice_per_idp),
  # not the older, session-based "wayf.remember_choice" feature: the two are mutually
  # exclusive and cannot both be enabled.
  #
  # The choice is stored in the "rememberedidps" cookie, so it must survive the loss of the
  # EngineBlock session. The second login in every scenario therefore starts without an
  # existing EB session: "I start a new browser session" clears only the PHP session
  # cookie and leaves "rememberedidps" in place. Without that step the legacy session
  # based mechanism could skip the WAYF and the scenario would prove nothing.

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "Dummy-IdP"
    And an Identity Provider named "Second-IdP"
    And a Service Provider named "Remembering-SP"
    And SP "Remembering-SP" allows remembering the WAYF choice
    And a Service Provider named "Non-Remembering-SP"

  Scenario: Remembering a choice skips the WAYF on a subsequent login
    When I log in at "Remembering-SP"
     And I select "Dummy-IdP" on the WAYF and remember my choice
     And I pass through EngineBlock
     And I pass through the IdP
    Then the "rememberedidps" cookie should remember "Dummy-IdP" for SP "Remembering-SP"
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/Remembering-SP/acs"
    When I start a new browser session
     And I log in at "Remembering-SP"
     And I pass through EngineBlock
    Then the url should not match "authentication/proxy/wayf"
     And the url should match "Dummy-IdP/sso"
    When I pass through the IdP
     And I pass through EngineBlock
    Then the url should match "functional-testing/Remembering-SP/acs"

  Scenario: The remembered choice is scoped per SP
    Given a Service Provider named "Other-Remembering-SP"
      And SP "Other-Remembering-SP" allows remembering the WAYF choice
      And my browser has remembered IdP "Dummy-IdP" for SP "Remembering-SP"
    When I log in at "Other-Remembering-SP"
    Then I should see "Select an account to login to"
    When I log in at "Remembering-SP"
     And I pass through EngineBlock
    Then the url should match "Dummy-IdP/sso"

  Scenario: An SP without the coin never gets the choice remembered
    When I log in at "Non-Remembering-SP"
     And I select "Dummy-IdP" on the WAYF and remember my choice
     And I pass through EngineBlock
     And I pass through the IdP
    Then the "rememberedidps" cookie should not be set
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/Non-Remembering-SP/acs"
    When I start a new browser session
     And I log in at "Non-Remembering-SP"
    Then I should see "Select an account to login to"

  Scenario: A remembered IdP that is no longer connected to the SP is ignored
    Given an Identity Provider named "Third-IdP"
      And my browser has remembered IdP "Dummy-IdP" for SP "Remembering-SP"
      And SP "Remembering-SP" is not connected to IdP "Dummy-IdP"
    When I log in at "Remembering-SP"
    Then I should see "Select an account to login to"

  Scenario: An expired remembered choice is ignored
    Given my browser has remembered IdP "Dummy-IdP" for SP "Remembering-SP" but that choice has expired
    When I log in at "Remembering-SP"
    Then I should see "Select an account to login to"

  Scenario: The reset endpoint clears a remembered choice
    Given my browser has remembered IdP "Dummy-IdP" for SP "Remembering-SP"
    When I go to Engineblock URL "/reset-remember-wayf"
    Then the "rememberedidps" cookie should not be set
    When I log in at "Remembering-SP"
    Then I should see "Select an account to login to"

  Scenario: The cookie removal page lists and removes the remembered choice
    Given my browser has remembered IdP "Dummy-IdP" for SP "Remembering-SP"
    When I go to Engineblock URL "/authentication/idp/remove-cookies"
    Then the response should contain 'rememberedidps'
    When I press "remove_rememberedidps"
    Then the "rememberedidps" cookie should not be set
