Feature:
  In order to perform an unsolicited single sign On
  As an IdP
  I want to be able to initiate a login

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy IdP"
      And a Service Provider named "Dummy SP"

  Scenario: An IdP can initiated a login
     When An IdP initiated Single Sign on for SP "Dummy SP" is triggered by IdP "Dummy IdP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the url should match "functional-testing/Dummy%20SP/acs"

  Scenario: An IdP initiates a login with a valid signing key
    When An IdP initiated Single Sign on for SP "Dummy SP" is triggered by IdP "Dummy IdP" and specifies a valid signing key
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/Dummy%20SP/acs"

  # Should result in a generic 500 error, the logs specify the problem in greater detail.
  Scenario: An IdP initiates a login with an SP identity id query parameter
    When An IdP initiated Single Sign on for SP "Dummy SP" with invalid parameter, by IdP "Dummy IdP"
    Then I should see "OpenConext - Error - An error occurred"

  Scenario: An IdP initiates a login with an invalid hash
     When An IdP initiated Single Sign on for SP "Dummy SP" is incorrectly triggered by IdP "Dummy IdP"
     Then the url should match "authentication/feedback/unknown-preselected-idp"

  # Trying to use a non existent key results in a generic 500 error, this is known, correct behavior
  Scenario: An IdP initiates a login with an invalid signing key
    When An IdP initiated Single Sign on for SP "Dummy SP" is triggered by IdP "Dummy IdP" and specifies an invalid signing key
    Then I should see "Error - unknown key id"
     And I should see "Key ID: does-not-exist"
