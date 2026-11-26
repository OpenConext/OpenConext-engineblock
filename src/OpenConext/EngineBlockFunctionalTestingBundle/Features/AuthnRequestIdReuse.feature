Feature:
  In order to make sure access rules are enforced for the Service Provider that started a login
  As an EngineBlock
  I want a pending login to stay bound to that Service Provider, even if another one reuses its AuthnRequest ID

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And an Identity Provider named "Reuse-IdP"
      And an Identity Provider named "Other-IdP"
      And a Service Provider named "Easy-SP"
      And a Service Provider named "Target-SP"
      And a Service Provider named "Proxy-SP"
      And SP "Easy-SP" is not connected to IdP "Other-IdP"

  Scenario: Control - a policy enforcement deny on Target-SP denies access
    Given SP "Target-SP" requires a policy enforcement decision
      And SP "Target-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
      And pdp gives a deny response
     When I log in at "Target-SP"
      And I select "Reuse-IdP" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Error - Access denied"

  Scenario: A policy enforcement deny on Target-SP is not bypassed by reusing the AuthnRequest ID
    Given SP "Target-SP" requires a policy enforcement decision
      And pdp gives a deny response
      And SP "Easy-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
      And SP "Target-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
     When I log in at "Easy-SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then the response should contain "Easy-SP will receive"
     When I log in at "Target-SP"
     Then I should see "Error - An error occurred"
     When I post consent for request ID "_authnrequestidreuse00000000000000000001"
     Then the response should not contain "/functional-testing/Target-SP/acs"
      And the response should contain "/functional-testing/Easy-SP/acs"

  Scenario: Control - an IdP that is not connected to Target-SP is not offered
    Given SP "Target-SP" is not connected to IdP "Reuse-IdP"
      And SP "Target-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
     When I log in at "Target-SP"
     Then the response should contain "/functional-testing/Other-IdP/sso"
      And the response should not contain "/functional-testing/Reuse-IdP/sso"

  Scenario: An IdP that is not connected to Target-SP cannot be used by reusing the AuthnRequest ID
    Given SP "Target-SP" is not connected to IdP "Reuse-IdP"
      And SP "Easy-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
      And SP "Target-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
     When I log in at "Easy-SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then the response should contain "Easy-SP will receive"
     When I log in at "Target-SP"
     Then I should see "Error - An error occurred"
     When I post consent for request ID "_authnrequestidreuse00000000000000000001"
     Then the response should not contain "/functional-testing/Target-SP/acs"
      And the response should contain "/functional-testing/Easy-SP/acs"

  Scenario: Control - a Stepup LoA required by Target-SP sends the user to Stepup
    Given the SP "Target-SP" requires Stepup LoA "http://dev.openconext.local/assurance/loa3"
      And SP "Target-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
     When I log in at "Target-SP"
      And I select "Reuse-IdP" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then the response should contain "/authentication/stepup/consume-assertion"

  Scenario: A Stepup LoA required by Target-SP is not bypassed by reusing the AuthnRequest ID
    Given the SP "Target-SP" requires Stepup LoA "http://dev.openconext.local/assurance/loa3"
      And SP "Easy-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
      And SP "Target-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
     When I log in at "Easy-SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then the response should contain "Easy-SP will receive"
     When I log in at "Target-SP"
     Then I should see "Error - An error occurred"
     When I post consent for request ID "_authnrequestidreuse00000000000000000001"
     Then the response should not contain "/functional-testing/Target-SP/acs"
      And the response should contain "/functional-testing/Easy-SP/acs"

  Scenario: Reusing the ID of a signed AuthnRequest of Target-SP is rejected as well
    Given SP "Target-SP" requires a policy enforcement decision
      And pdp gives a deny response
      And SP "Target-SP" signs its requests
      And SP "Easy-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
      And SP "Target-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
     When I log in at "Easy-SP"
      And I pass through EngineBlock
      And I pass through the IdP
     Then the response should contain "Easy-SP will receive"
     When I log in at "Target-SP"
     Then I should see "Error - An error occurred"
     When I post consent for request ID "_authnrequestidreuse00000000000000000001"
     Then the response should not contain "/functional-testing/Target-SP/acs"
      And the response should contain "/functional-testing/Easy-SP/acs"

  Scenario: The same SP can resubmit an AuthnRequest with the same ID
    Given SP "Easy-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
     When I log in at "Easy-SP"
      And I log in at "Easy-SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I pass through EngineBlock
     Then the url should match "/functional-testing/Easy-SP/acs"

  Scenario: Control - an SP behind a trusted proxy that requires Stepup LoA3 sends the user to Stepup for LoA3
    Given SP "Proxy-SP" is a trusted proxy
      And SP "Proxy-SP" signs its requests
      And SP "Proxy-SP" does not require consent
      And SP "Target-SP" does not require consent
      And the SP "Target-SP" requires Stepup LoA "http://dev.openconext.local/assurance/loa3"
      And SP "Proxy-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
      And SP "Proxy-SP" is authenticating for SP "Target-SP"
     When I log in at "Proxy-SP"
      And I select "Reuse-IdP" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then the received AuthnRequest should match xpath '//saml:AuthnContextClassRef[text()="http://dev.openconext.local/assurance/loa3"]'

  Scenario: A Stepup LoA required by an SP behind a trusted proxy is not bypassed by reusing the AuthnRequest ID for it
    Given SP "Proxy-SP" is a trusted proxy
      And SP "Proxy-SP" signs its requests
      And SP "Proxy-SP" does not require consent
      And SP "Easy-SP" does not require consent
      And SP "Target-SP" does not require consent
      And the SP "Easy-SP" requires Stepup LoA "http://dev.openconext.local/assurance/loa2"
      And the SP "Target-SP" requires Stepup LoA "http://dev.openconext.local/assurance/loa3"
      And SP "Easy-SP" has the following Attribute Manipulation:
      """
      $attributes['nl:surf:test:easy-sp'] = array("easy");
      """
      And SP "Target-SP" has the following Attribute Manipulation:
      """
      $attributes['nl:surf:test:target-sp'] = array("target");
      """
      And SP "Proxy-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
      And SP "Proxy-SP" is authenticating for SP "Easy-SP"
     When I log in at "Proxy-SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I keep the Stepup "success" response for later
      And SP "Proxy-SP" is authenticating for SP "Target-SP" resetting the RequesterID chain
      And I log in at "Proxy-SP"
     Then I should see "Error - An error occurred"
     When I submit the kept Stepup response
      And I pass through EngineBlock
     Then the url should match "/functional-testing/Proxy-SP/acs"
      And the response should contain "nl:surf:test:easy-sp"
      And the response should not contain "nl:surf:test:target-sp"

  Scenario: The same SP behind a trusted proxy can resubmit an AuthnRequest with the same ID
    Given SP "Proxy-SP" is a trusted proxy
      And SP "Proxy-SP" signs its requests
      And SP "Proxy-SP" does not require consent
      And SP "Easy-SP" does not require consent
      And SP "Proxy-SP" uses AuthnRequest ID "_authnrequestidreuse00000000000000000001"
      And SP "Proxy-SP" is authenticating for SP "Easy-SP"
     When I log in at "Proxy-SP"
      And I log in at "Proxy-SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I pass through EngineBlock
     Then the url should match "/functional-testing/Proxy-SP/acs"
