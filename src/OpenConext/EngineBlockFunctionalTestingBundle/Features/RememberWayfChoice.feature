Feature:
  In order to not have to pick my IdP every time I log in to the same SP
  As a user
  I want EngineBlock to remember my previous IdP choice for that SP

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
    Then the "rememberedidps" cookie should be set
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/Remembering-SP/acs"
    And I start a new browser session
    When I log in at "Remembering-SP"
     And I pass through EngineBlock
    Then the url should not match "authentication/proxy/wayf"
    And the url should match "Dummy-IdP/sso"
    When I pass through the IdP
    When I pass through EngineBlock
    Then the url should match "functional-testing/Remembering-SP/acs"

  Scenario: The remembered choice is scoped per SP
    Given a Service Provider named "Other-Remembering-SP"
    And SP "Other-Remembering-SP" allows remembering the WAYF choice
    When I log in at "Remembering-SP"
     And I select "Dummy-IdP" on the WAYF and remember my choice
     And I pass through EngineBlock
     And I pass through the IdP
    Then the "rememberedidps" cookie should be set
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/Remembering-SP/acs"
    And I start a new browser session
    When I log in at "Other-Remembering-SP"
     And I select "Second-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/Other-Remembering-SP/acs"

  Scenario: An SP without the coin never gets the choice remembered
    When I log in at "Non-Remembering-SP"
     And I select "Dummy-IdP" on the WAYF and remember my choice
     And I pass through EngineBlock
     And I pass through the IdP
    Then the "rememberedidps" cookie should not be set
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/Non-Remembering-SP/acs"
    And I start a new browser session
    When I log in at "Non-Remembering-SP"
     And I select "Dummy-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/Non-Remembering-SP/acs"

  Scenario: The reset endpoint clears a remembered choice
    When I log in at "Remembering-SP"
     And I select "Dummy-IdP" on the WAYF and remember my choice
     And I pass through EngineBlock
     And I pass through the IdP
    Then the "rememberedidps" cookie should be set
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/Remembering-SP/acs"
    When I go to Engineblock URL "/reset-remember-wayf"
    Then the "rememberedidps" cookie should not be set
    And I start a new browser session
    When I log in at "Remembering-SP"
     And I select "Dummy-IdP" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/Remembering-SP/acs"
