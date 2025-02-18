Feature:
  In order to limit leakage of unnecessary user data
  As an OpenConext admin
  I need EB to apply Attribute Release Policies

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "TestIdp"
    And a Service Provider named "No ARP"
    And a Service Provider named "Empty ARP"
    And a Service Provider named "Wildcard ARP"
    And a Service Provider named "Wrong Value ARP"
    And a Service Provider named "Right Value ARP"
    And a Service Provider named "Specific Value ARP"
    And a Service Provider named "Two value ARP"
    And a Service Provider named "Trusted Proxy"
    And a Service Provider named "Stepup Gateway"
    And a Service Provider named "Stepup SelfService"
    And a Service Provider named "Release As"
    And a Service Provider named "Use as NameID"
    And a Service Provider named "Use as NameID and Release As"
    And SP "Empty ARP" allows no attributes
    And SP "Wildcard ARP" allows an attribute named "urn:mace:dir:attribute-def:uid"
    And SP "Wrong Value ARP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization" with value "example.edu"
    And SP "Right Value ARP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization" with value "engine-test-stand.openconext.org"
    And SP "Specific Value ARP" allows an attribute named "urn:mace:dir:attribute-def:eduPersonAffiliation" with value "faculty"
    And SP "Two value ARP" allows an attribute named "urn:mace:dir:attribute-def:uid"
    And SP "Two value ARP" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And SP "Stepup Gateway" allows an attribute named "urn:mace:dir:attribute-def:uid"
    And SP "Stepup Gateway" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And SP "Stepup Gateway" allows an attribute named "urn:mace:terena.org:attribute-def:eduPersonAffiliation"
    And SP "Stepup SelfService" allows an attribute named "urn:mace:dir:attribute-def:uid"
    And SP "Release As" allows an attribute named "urn:mace:dir:attribute-def:uid" released as "Kustom-UiD"
    And SP "Use as NameID" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And SP "Use as NameID" uses the value of attribute "urn:mace:terena.org:attribute-def:schacHomeOrganization" as the NameId
    And SP "Use as NameID and Release As" allows an attribute named "urn:mace:terena.org:attribute-def:schacHomeOrganization" released as "Kustom-schacHomeOrganization"
    And SP "Use as NameID and Release As" uses the value of attribute "urn:mace:terena.org:attribute-def:schacHomeOrganization" as the NameId
    And feature "eb.run_all_manipulations_prior_to_consent" is disabled

  Scenario: As a user for an Idp SP without ARPs I get all attributes
    When I log in at "No ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP with an empty ARP I get no attributes
    When I log in at "Empty ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP with a wildcard ARP I get all values for that attribute
    When I log in at "Wildcard ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP with a specific value ARP I do not see the attribute with a wrong value
    When I log in at "Wrong Value ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP renaming the uid attribute in the ARP I do not see the original attribute but the alias
    When I log in at "Release As"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "Kustom-UiD"
    When I give my consent
    And I pass through EngineBlock
    # The release_as logic is applied after consent
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "Kustom-UiD"

  Scenario: As a user for an SP the ARP can overwrite the NameId with a given attribute value
    When I log in at "Use as NameID"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    # The NameID is overwritten with the value of the schacHomeOrganization
    # The IdP always releases the value: engine-test-stand.openconext.org for this schacHomeOrganization
    # See: MockIdentityProviderFactory::generateDefaultResponse
    Then the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    # The name id always becomes unspecified after substitution
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="engine-test-stand.openconext.org"]'

  Scenario: As a user for an SP the ARP can overwrite the NameId with a given attribute value and rename the attribute at the same time
    When I log in at "Use as NameID and Release As"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And the response should not contain "Kustom-schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And the response should contain "Kustom-schacHomeOrganization"
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:Subject/saml:NameID[@Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified" and text()="engine-test-stand.openconext.org"]'

  Scenario: As a user for an SP with a specific value ARP I do see the attribute if it has the right value
    When I log in at "Right Value ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP with a specific value ARP and multiple attributes sent I do see the attribute with only the right value
    Given the IdP "TestIdp" sends attribute "urn:mace:dir:attribute-def:eduPersonAffiliation" with values "student,faculty,guest,member" and xsi:type is "xs:string"
    When I log in at "Specific Value ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:dir:attribute-def:eduPersonAffiliation"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:dir:attribute-def:eduPersonAffiliation"
    And the response should contain "faculty"
    And the response should not contain "member"
    And the response should not contain "guest"

  Scenario: As a user for an SP with 2 attributes in the ARP I only get those attributes
    When I log in at "Two value ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP behind TP without ARP I get all attributes
    Given SP "Trusted Proxy" is authenticating for SP "No ARP"
    And SP "Trusted Proxy" is a trusted proxy
    And SP "Trusted Proxy" signs its requests
    When I log in at "Trusted Proxy"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP behind TP with an empty ARP I get no attributes
    Given SP "Trusted Proxy" is authenticating for SP "Empty ARP"
    And SP "Trusted Proxy" is a trusted proxy
    And SP "Trusted Proxy" signs its requests
    When I log in at "Trusted Proxy"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP behind TP with a wildcard ARP I get all values for that attribute
    Given SP "Trusted Proxy" is authenticating for SP "Wildcard ARP"
    And SP "Trusted Proxy" is a trusted proxy
    And SP "Trusted Proxy" signs its requests
    When I log in at "Trusted Proxy"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP behind TP with a specific value ARP I do not see the attribute with a wrong value
    Given SP "Trusted Proxy" is authenticating for SP "Wrong Value ARP"
    And SP "Trusted Proxy" is a trusted proxy
    And SP "Trusted Proxy" signs its requests
    When I log in at "Trusted Proxy"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP behind TP with a specific value ARP I do see the attribute if it has the right value
    Given SP "Trusted Proxy" is authenticating for SP "Right Value ARP"
    And SP "Trusted Proxy" is a trusted proxy
    And SP "Trusted Proxy" signs its requests
    When I log in at "Trusted Proxy"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    When I give my consent
    And I pass through EngineBlock
    Then the response should not contain "urn:mace:dir:attribute-def:uid"
    And the response should contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"

  Scenario: As a user for an SP behind TP both ARPs are applied leaving an intersection of the attributes
    Given SP "Stepup Gateway" is authenticating for SP "Stepup SelfService"
    And SP "Stepup Gateway" is a trusted proxy
    And SP "Stepup Gateway" signs its requests
    When I log in at "Stepup Gateway"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
    And the response should not contain "urn:mace:terena.org:attribute-def:eduPersonAffiliation"
    When I give my consent
    And I pass through EngineBlock
    Then the response should contain "urn:mace:dir:attribute-def:uid"
    Then the response should not contain "urn:mace:dir:attribute-def:eduPersonAffiliation"
    And the response should not contain "urn:mace:terena.org:attribute-def:schacHomeOrganization"
