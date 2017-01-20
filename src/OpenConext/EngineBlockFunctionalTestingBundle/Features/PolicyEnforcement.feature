Feature:
  In order to enable IdPs to deny or allow certain users access to an SP
  As an EngineBlock
  I want to enforce the configured policies

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Dummy IdP"
      And a Service Provider named "Dummy SP"

  @WIP
  Scenario: Access is denied because of a Deny policy
    Given SP "Dummy SP" requires a policy enforcement decision
      And pdp gives a deny response
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I should see "Error - No access"
      And I should see "Message from your institution:"

  @WIP @MEH
  Scenario: Access is denied because of an Indeterminate policy
    Given SP "Dummy SP" requires a policy enforcement decision
      And pdp gives an indeterminate response
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I should see "Error - No access"
      And I should see "Message from your institution:"

  @WIP
  Scenario: Access is permitted because of a Permit policy
    Given SP "Dummy SP" requires a policy enforcement decision
      And pdp gives a permit response
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I should not see "Error - No access"

  @WIP
  Scenario: Access is permitted because of a Not Applicable policy
    Given SP "Dummy SP" requires a policy enforcement decision
      And pdp gives a not applicable response
     When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I should not see "Error - No access"
