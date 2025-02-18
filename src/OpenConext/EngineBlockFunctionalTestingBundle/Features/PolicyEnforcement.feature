Feature:
  In order to enable IdPs to deny or allow certain users access to an SP
  As an EngineBlock
  I want to enforce the configured policies

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy IdP" with logo "idp-logo.jpg"
      And a Service Provider named "Dummy SP"
      And a Service Provider named "Stepup Gateway"
      And a Service Provider named "Stepup SelfService"

  Scenario: Access is denied because of an IdP specific Deny policy a logo is shown
    Given SP "Dummy SP" requires a policy enforcement decision
    And pdp gives an IdP specific deny response for "MyIdP"
    When I log in at "Dummy SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I should see "Error - Access denied"
    And I should see "Message from your organisation:"
    And I should see "Students of MyIdP do not have access to this resource"
    And the response should contain "idp-logo.jpg"

  Scenario: Access is denied because of a Deny policy
    Given SP "Dummy SP" requires a policy enforcement decision
      And pdp gives a deny response
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I should see "Error - Access denied"
      And I should see "Message from your organisation:"

  Scenario: Access is denied because of an Indeterminate policy
    Given SP "Dummy SP" requires a policy enforcement decision
      And pdp gives an indeterminate response
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I should see "Error - Access denied"
      And I should see "Message from your organisation:"

  Scenario: Access is permitted because of a Permit policy
    Given SP "Dummy SP" requires a policy enforcement decision
      And pdp gives a permit response
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I should not see "Error - Access denied"

  Scenario: Access is permitted because of a Not Applicable policy
    Given SP "Dummy SP" requires a policy enforcement decision
      And pdp gives a not applicable response
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I should not see "Error - Access denied"

  Scenario: Access is denied because of a Deny policy, End-SP behind Trusted Proxy
    Given SP "Stepup SelfService" requires a policy enforcement decision
    And SP "Stepup Gateway" is authenticating for SP "Stepup SelfService"
    And SP "Stepup Gateway" is a trusted proxy
    And SP "Stepup Gateway" signs its requests
    And pdp gives a deny response
    When I log in at "Stepup Gateway"
    And I pass through EngineBlock
    And I pass through the IdP
    And I should see "Error - Access denied"
    And I should see "Message from your organisation:"

  Scenario: Access is denied because of an Indeterminate policy, End-SP behind Trusted Proxy
    Given SP "Stepup SelfService" requires a policy enforcement decision
    And SP "Stepup Gateway" is authenticating for SP "Stepup SelfService"
    And SP "Stepup Gateway" is a trusted proxy
    And SP "Stepup Gateway" signs its requests
    And pdp gives an indeterminate response
    When I log in at "Stepup Gateway"
    And I pass through EngineBlock
    And I pass through the IdP
    And I should see "Error - Access denied"
    And I should see "Message from your organisation:"

  Scenario: Access is permitted because of a Permit policy, End-SP behind Trusted Proxy
    Given SP "Stepup SelfService" requires a policy enforcement decision
    And SP "Stepup Gateway" is authenticating for SP "Stepup SelfService"
    And SP "Stepup Gateway" is a trusted proxy
    And SP "Stepup Gateway" signs its requests
    And pdp gives a permit response
    When I log in at "Stepup Gateway"
    And I pass through EngineBlock
    And I pass through the IdP
    And I should not see "Error - Access denied"

  Scenario: Access is permitted because of a Not Applicable policy, End-SP behind Trusted Proxy
    Given SP "Stepup SelfService" requires a policy enforcement decision
    And SP "Stepup Gateway" is authenticating for SP "Stepup SelfService"
    And SP "Stepup Gateway" is a trusted proxy
    And SP "Stepup Gateway" signs its requests
    And pdp gives a not applicable response
    When I log in at "Stepup Gateway"
    And I pass through EngineBlock
    And I pass through the IdP
    And I should not see "Error - Access denied"
