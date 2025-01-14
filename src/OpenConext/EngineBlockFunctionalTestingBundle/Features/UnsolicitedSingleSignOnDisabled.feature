Feature:
  In order to disable unsolicited single sign On
  As an administrator
  I want to be able to disable unsolicited login

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And feature "eb.feature_enable_idp_initiated_flow" is disabled
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "Dummy IdP"
    And a Service Provider named "Dummy SP"

  # The feature flag: eb.feature_enable_idp_initiated_flow can disable unsolicited login
  # EB Shows a 404 page in that case as the entire HTTP route is blocked in that case
  Scenario: Engine disallows unsolicited login
    When An IdP initiated Single Sign on for SP "Dummy SP" is triggered by IdP "Dummy IdP"
    Then I should see "404 - Page not found"
