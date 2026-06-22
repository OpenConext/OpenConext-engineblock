Feature:
  In order to prevent users from saving a broken SAML URL as a bookmark
  As EngineBlock
  I want to hide the SAMLRequest from the browser's address bar

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy Idp"
      And a Service Provider named "Dummy SP"

  Scenario: A GET to the SSO endpoint without a SAMLRequest shows an error
    When I log in at "Dummy SP"
     And I go to Engineblock URL "/authentication/idp/single-sign-on"
    Then I should see "The parameter \"SAMLRequest\" is missing on the SAML SSO request"
