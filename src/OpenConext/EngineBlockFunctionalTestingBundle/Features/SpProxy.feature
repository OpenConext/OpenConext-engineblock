Feature:
  In order to increase my level of assurance
  As a user
  I need EB to proxy for my Step Up proxy

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "AlwaysAuth"
      And an Identity Provider named "StepUpOnlyAuth"
      And an Identity Provider named "LoaOnlyAuth"
      And an Identity Provider named "CombinedAuth"
      And a Service Provider named "Step Up"
      And a Service Provider named "Loa SP"
      And a Service Provider named "Far SP"
      And a Service Provider named "Test SP"
      And a Service Provider named "Second SP"
      And an unregistered Service Provider named "Unregistered SP"
      And SP "Far SP" is not connected to IdP "CombinedAuth"
      And SP "Far SP" is not connected to IdP "LoaOnlyAuth"
      And SP "Far SP" is not connected to IdP "StepUpOnlyAuth"
      And SP "Loa SP" is not connected to IdP "StepUpOnlyAuth"
      And SP "Step Up" is not connected to IdP "LoaOnlyAuth"
      And SP "Test SP" is not connected to IdP "CombinedAuth"
      And SP "Test SP" is not connected to IdP "LoaOnlyAuth"
      And SP "Test SP" is not connected to IdP "StepUpOnlyAuth"
      And SP "Test SP" is using workflow state "testaccepted"

  Scenario: User logs in to the SP without a proxy and wayf shows relevant Identity Providers
    When I log in at "Loa SP"
    Then I should see "AlwaysAuth"
     And I should see "CombinedAuth"
     And I should see "LoaOnlyAuth"
     And I should not see "StepUpOnlyAuth"

  Scenario: User logs in to the proxy without a SP and wayf shows relevant Identity Providers
    When I log in at "Step Up"
    Then I should see "AlwaysAuth"
    And I should see "CombinedAuth"
    And I should see "StepUpOnlyAuth"
    And I should not see "LoaOnlyAuth"

  Scenario: User logs in to the trusted proxy, wayf shows relevant Identity Providers
    Given SP "Step Up" is authenticating for SP "Loa SP"
    And SP "Step Up" is a trusted proxy
    And SP "Step Up" signs its requests
    When I log in at "Step Up"
    Then I should see "AlwaysAuth"
    And I should see "CombinedAuth"
    And I should not see "StepUpOnlyAuth"
    And I should not see "LoaOnlyAuth"

  Scenario: User logs in via untrusted proxy accesses discovery for unknown SP
    Given SP "Step Up" is authenticating and uses RequesterID "https://example.edu/saml2/metadata"
     When I log in at "Step Up"
     Then I should see "AlwaysAuth"
      And I should see "CombinedAuth"
      And I should see "StepUpOnlyAuth"
      And I should not see "LoaOnlyAuth"

  Scenario: User logs in via untrusted proxy accesses discovery for known SP, sees less IdPs
    Given SP "Step Up" is authenticating for SP "Loa SP"
     When I log in at "Step Up"
     Then I should see "AlwaysAuth"
      And I should see "CombinedAuth"
      # In order to gain access to an IdP through a SP Proxy, the SP Proxy also needs access to the IdP
      And I should not see "LoaOnlyAuth"
      And I should not see "StepUpOnlyAuth"

  Scenario: User logs in via untrusted proxy accesses discovery for known SP, sees less IdPs
    Given SP "Step Up" is authenticating for SP "Loa SP"
     When I log in at "Step Up"
     Then I should see "AlwaysAuth"
      And I should see "CombinedAuth"
      # In order to gain access to an IdP through a SP Proxy, the SP Proxy also needs access to the IdP
      And I should not see "LoaOnlyAuth"
      And I should not see "StepUpOnlyAuth"

  Scenario: User logs in via untrusted proxy for destination without consent and sees consent for proxy anyway
    Given SP "Step Up" is authenticating for SP "Loa SP"
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "needs this information to function properly"
      And I should see "Step Up"
      And I should not see "Loa SP"

  Scenario: User logs in via trusted signing proxy and sees consent for the destination
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs its requests
     When I log in at "Step Up"
     Then I should see "Select an account to login to Loa SP"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "needs this information to function properly"
      And I should see "Loa SP"
      And I should not see "Step Up"

  Scenario: User logs in via trusted proxy proxy and sees consent for the proxy proxy (only 1 level)
    Given SP "Step Up" is authenticating for SP "Far SP"
      And SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs its requests
     When I log in at "Step Up"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "needs this information to function properly"
      And I should not see "Far SP"
      And I should not see "Step Up"
      And I should see "Loa SP"

  Scenario: User logs in via trusted proxy and sees no consent as the destination has it disabled
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs its requests
      And SP "Loa SP" does not require consent
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should not see "needs this information to function properly"

  Scenario: User logs in via trusted proxy and sees no consent as the destination has it disabled
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs its requests
      And SP "Step Up" does not require consent
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should not see "needs this information to function properly"

  Scenario: User logs in via trusted proxy and attribute release policy for destination is executed
    Given SP "Step Up" is authenticating for SP "Loa SP"
    And SP "Step Up" is a trusted proxy
    And SP "Step Up" signs its requests
    And SP "Step Up" does not require consent
    And SP "Loa SP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I log in at "Step Up"
    And I select "AlwaysAuth" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: User logs in via trusted proxy and attribute manipulation for proxy and destination are executed
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs its requests
      And SP "Step Up" does not require consent
      And SP "Step Up" has the following Attribute Manipulation:
      """
      $attributes['nl:surf:test:step-up'] = array("your game son");
      """
      And SP "Loa SP" has the following Attribute Manipulation:
      """
      $attributes['nl:surf:test:loa-sp'] = array("the only assurance is that there are no assurances");
      """
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
      And I pass through EngineBlock
     Then the response should contain "nl:surf:test:step-up"
      And the response should contain "nl:surf:test:loa-sp"

  Scenario: User logs in via trusted proxy and attribute release policy for proxy and destination are executed
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs its requests
      And SP "Step Up" does not require consent
      And SP "Loa SP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
      And I pass through EngineBlock
     Then the response should not contain "urn:mace:dir:attribute-def:uid"
      And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: User logs in via trusted proxy and attribute release policy for proxy and destination are executed
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs its requests
      And SP "Step Up" does not require consent
      And SP "Step Up" allows an attribute named "urn:mace:dir:attribute-def:uid"
      And SP "Loa SP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
      And I pass through EngineBlock
     Then the response should not contain "urn:mace:dir:attribute-def:uid"
      And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: Stepup authentication should be supported if set through PDP when End-SP is behind TP
    Given SP "Step Up" is authenticating for SP "Loa SP"
    And SP "Step Up" is a trusted proxy
    And SP "Step Up" signs its requests
    And SP "Step Up" does not require consent
    And SP "Loa SP" does not require consent
    And SP "Loa SP" requires a policy enforcement decision
    And pdp gives a stepup obligation response for "http://dev.openconext.local/assurance/loa3"
    When I log in at "Step Up"
    And I select "AlwaysAuth" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    And Stepup will successfully verify a user
    And I pass through EngineBlock
    Then the url should match "/functional-testing/Step%20Up/acs"

  Scenario: User logs in at test SP and via prod trusted proxy and is denied access
    Given SP "Step Up" is authenticating for SP "Test SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs its requests
      And SP "Step Up" does not require consent
      And SP "Step Up" uses the Unspecified NameID format
    When I log in at "Step Up"
    Then I should see "Error - Unknown service"
     And I should see "Proxy SP:"

  Scenario: User logs in via misconfigured trusted proxy and sees error
    Given SP "Step Up" is authenticating for misconfigured SP "Far SP"
    And SP "Step Up" is a trusted proxy
    And SP "Step Up" signs its requests
    When I log in at "Step Up"
    Then I should see "Error - Unknown service"

  Scenario: User logs in via trusted proxy which requests unknown SP and sees error
    Given SP "Step Up" is authenticating for SP "Unregistered SP"
    And SP "Step Up" is a trusted proxy
    And SP "Step Up" signs its requests
    When I log in at "Step Up"
    Then I should see "Error - Unknown service"
     And I should see "UR ID:"
     And I should see "EC:"
     And I should see "SP:"
     And I should see "Proxy SP:"

  Scenario: User logs in to two SPs via trusted proxy
   Given SP "Step Up" is authenticating for SP "Loa SP"
     And SP "Step Up" is a trusted proxy
     And SP "Step Up" signs its requests
     And SP "Step Up" does not require consent
    When I log in at "Step Up"
     And I select "AlwaysAuth" on the WAYF
     And I pass through EngineBlock
     And I pass through the IdP
     And I pass through EngineBlock
         # Next, Step Up SP would redirect to the Loa SP, but verifying this is out of our test boundaries
    Then the url should match "functional-testing/Step%20Up/acs"
   Given SP "Step Up" is authenticating for SP "Second SP" resetting the RequesterID chain
     And SP "Second SP" is not connected to IdP "AlwaysAuth"
    When I log in at "Second SP"
         # Bug report: https://www.pivotaltracker.com/story/show/164069793
    Then I should not see "Error - No organisations found"
         # The WAYF should be visible
     And I should see "Select an account to login to"

  Scenario: Trusted proxy not signing requests results in an error
    Given SP "Step Up" is authenticating for SP "Loa SP"
    And SP "Step Up" is a trusted proxy
    When I log in at "Step Up"
    Then I should see "Error - An error occurred"
