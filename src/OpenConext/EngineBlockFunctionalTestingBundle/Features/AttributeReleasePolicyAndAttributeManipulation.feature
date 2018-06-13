Feature:
  In order to be able to influence the released attribute values
  As an IdP or SP
  I want to be able to manipulate the response attributes through configured code in combination with attribute manipulation

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "IdP-with-Attribute-Manipulation"
    And the IdP "IdP-with-Attribute-Manipulation" sends attribute "urn:mace:dir:attribute-def:eduPersonAffiliation" with values "student,affiliate,member" and xsi:type is "xs:string"
    And a Service Provider named "SP-with-ARP"
    And a Service Provider named "SP-with-ARP-and-Attribute-Manipulation"
    And a Service Provider named "SP-with-Attribute-Manipulation"
    And SP "SP-with-ARP" allows an attribute named "urn:mace:dir:attribute-def:eduPersonAffiliation" with value "member"
    And SP "SP-with-ARP-and-Attribute-Manipulation" allows an attribute named "urn:mace:dir:attribute-def:eduPersonAffiliation" with value "student"

  Scenario: The Service Provider can apply arp with attribute manipulation enabled with typed attribute values in combination with ARP
    Given SP "SP-with-ARP-and-Attribute-Manipulation" has the following Attribute Manipulation:
      """
      $attributes['urn:mace:dir:attribute-def:mail'][] = 'john@example.co.uk';
      """
    When I log in at "SP-with-ARP-and-Attribute-Manipulation"
     And I pass through EngineBlock
     And I pass through the IdP
     Then the response should not contain "john@example.co.uk"
    When I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-ARP-and-Attribute-Manipulation/acs"
     And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonAffiliation"]/saml:AttributeValue[text()="student"]'

  Scenario: The Service Provider with typed attribute values and ARP
    When I log in at "SP-with-ARP"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "john@example.co.uk"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-ARP/acs"
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonAffiliation"]/saml:AttributeValue[text()="member"]'

  Scenario: The Service Provider with typed attribute values and attribute manipulations
    Given SP "SP-with-Attribute-Manipulation" has the following Attribute Manipulation:
      """
      $attributes['urn:mace:dir:attribute-def:mail'][] = 'john@example.co.uk';
      """
    When I log in at "SP-with-Attribute-Manipulation"
    And I pass through EngineBlock
    And I pass through the IdP
    Then the response should not contain "john@example.co.uk"
    When I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/SP-with-Attribute-Manipulation/acs"
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonAffiliation"]/saml:AttributeValue[text()="affiliate"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonAffiliation"]/saml:AttributeValue[text()="student"]'
    And the response should match xpath '/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute[@Name="urn:mace:dir:attribute-def:eduPersonAffiliation"]/saml:AttributeValue[text()="member"]'
