Feature:
  In order to increase my level of assurance
  As a user
  I need EB to proxy for my Step Up proxy

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
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
      And IdP "AlwaysAuth" uses a blacklist for access control
      And IdP "StepUpOnlyAuth" uses a whitelist for access control
      And IdP "StepUpOnlyAuth" whitelists SP "Step Up"
      And IdP "LoaOnlyAuth" uses a whitelist for access control
      And IdP "LoaOnlyAuth" whitelists SP "Loa SP"
      And IdP "CombinedAuth" uses a whitelist for access control
      And IdP "CombinedAuth" whitelists SP "Step Up"
      And IdP "CombinedAuth" whitelists SP "Loa SP"
      And SP "Step Up" uses a whitelist for access control
      And SP "Step Up" whitelists IdP "AlwaysAuth"
      And SP "Step Up" whitelists IdP "StepUpOnlyAuth"
      And SP "Step Up" whitelists IdP "CombinedAuth"
      And SP "Loa SP" uses a whitelist for access control
      And SP "Loa SP" whitelists IdP "AlwaysAuth"
      And SP "Loa SP" whitelists IdP "LoaOnlyAuth"
      And SP "Loa SP" whitelists IdP "CombinedAuth"
      And SP "Far SP" uses a blacklist for access control
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
     Then I should see "Request for release of your information"
      And I should see "Step Up"
      And I should not see "Loa SP"

  Scenario: User logs in via trusted proxy and sees consent for the destination
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      # Test to see that we don't trust trusted proxies without request signing
      #And SP "Step Up" signs it's requests
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Request for release of your information"
      And I should see "Step Up"
      And I should not see "Loa SP"

  Scenario: User logs in via trusted proxy and sees consent for the destination
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs it's requests
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Request for release of your information"
      And I should see "Loa SP"
      And I should not see "Step Up"

  Scenario: User logs in via trusted proxy proxy and sees consent for the proxy proxy (only 1 level)
    Given SP "Step Up" is authenticating for SP "Far SP"
      And SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs it's requests
     When I log in at "Step Up"
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should see "Request for release of your information"
      And I should not see "Far SP"
      And I should not see "Step Up"
      And I should see "Loa SP"

  Scenario: User logs in via trusted proxy and sees no consent as the destination has it disabled
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs it's requests
      And SP "Loa SP" does not require consent
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should not see "Request for release of your information"

  Scenario: User logs in via trusted proxy and sees no consent as the destination has it disabled
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs it's requests
      And SP "Step Up" does not require consent
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
     Then I should not see "Request for release of your information"

  Scenario: User logs in via trusted proxy and attribute manipulation for proxy and destination are executed
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs it's requests
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
      And SP "Step Up" signs it's requests
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
      And SP "Step Up" signs it's requests
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

  Scenario: User logs in via trusted proxy and I get a NameID for the SP and eduPersonTargettedID for the destination
    Given SP "Step Up" is authenticating for SP "Loa SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs it's requests
      And SP "Step Up" does not require consent
      And SP "Step Up" uses the Unspecified NameID format
     When I log in at "Step Up"
      And I select "AlwaysAuth" on the WAYF
      And I pass through EngineBlock
      And I pass through the IdP
      And I pass through EngineBlock
     Then the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"]'
      And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonTargetedID"]/saml:AttributeValue/saml:NameID[@Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"]'

  Scenario: User logs in at test SP and via prod trusted proxy and is denied access
    Given SP "Step Up" is authenticating for SP "Test SP"
      And SP "Step Up" is a trusted proxy
      And SP "Step Up" signs it's requests
      And SP "Step Up" does not require consent
      And SP "Step Up" uses the Unspecified NameID format
     When I log in at "Step Up"
      And print last response
     Then I should see "Dissimilar workflow states"
