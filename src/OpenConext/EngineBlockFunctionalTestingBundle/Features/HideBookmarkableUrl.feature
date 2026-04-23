Feature:
  In order to prevent users from saving a broken SAML URL as a bookmark
  As EngineBlock
  I want to hide the SAMLRequest from the browser's address bar
  while still recovering gracefully when the user presses the Back button

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy Idp"
      And an Identity Provider named "Second Idp"
      And a Service Provider named "Dummy SP"

  Scenario: Pressing Back after visiting the WAYF restores the SAML request from the session
    When I log in at "Dummy SP"
     And I go to Engineblock URL "/authentication/idp/single-sign-on"
    Then I should see "Dummy Idp"

  Scenario: A GET to the SSO endpoint without an active session shows an error
    When I log in at "Dummy SP"
     And I lose my session
     And I go to Engineblock URL "/authentication/idp/single-sign-on"
    Then I should see "The parameter \"SAMLRequest\" is missing on the SAML SSO request"
