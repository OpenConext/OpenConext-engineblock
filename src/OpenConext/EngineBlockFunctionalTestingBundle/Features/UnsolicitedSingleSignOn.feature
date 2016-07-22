Feature:
  In order to perform an unsolicited single sign On
  As an IdP
  I want to be able to initiate a login

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
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

  Scenario: An IdP initiates a login with an invalid hash
     When An IdP initiated Single Sign on for SP "Dummy SP" is incorrectly triggered by IdP "Dummy IdP"
     Then the url should match "authentication/feedback/unknown-preselected-idp"
