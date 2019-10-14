Feature:
  In order to ensure EngineBlock interoperability
  As an user
  I want to be able to share EngineBlock metadata

  Background:
    Given an EngineBlock instance on "vm.openconext.org"
    And no registered SPs
    And no registered Idps

  Scenario: A user can request the EngineBlock SP Proxy metadata
      When I go to Engineblock URL "/authentication/sp/metadata"
      # Verify the entity id is correctly set in the metadata
      Then the response should match xpath '/md:EntityDescriptor[@entityID="https://engine.vm.openconext.org/authentication/sp/metadata"]'
      # Verify the display name (EN) correctly set in the metadata
      And the response should match xpath '//mdui:DisplayName[@xml:lang="en" and text()="OpenConext EngineBlock"]'
      # Verify the signature method is set to sha256
      And the response should match xpath '//ds:SignatureMethod[@Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"]'
      # Verify the ACS location and binding
      And the response should match xpath '//md:AssertionConsumerService[@Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" and @Location="https://engine.vm.openconext.org/authentication/sp/consume-assertion"]'

  Scenario: A user can request the EngineBlock IdP Proxy metadata
      When I go to Engineblock URL "/authentication/idp/metadata"
      # Verify the entity id is correctly set in the metadata
      Then the response should match xpath '/md:EntityDescriptor[@entityID="https://engine.vm.openconext.org/authentication/idp/metadata"]'
      # Verify the display name (EN) correctly set in the metadata
      And the response should match xpath '//mdui:DisplayName[@xml:lang="en" and text()="OpenConext EngineBlock"]'
      # Verify the signature method is set to sha256
      And the response should match xpath '//ds:SignatureMethod[@Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"]'
      # Verify SSO location and binding is set correctly
      And the response should match xpath '//md:SingleSignOnService[@Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" and @Location="https://engine.vm.openconext.org/authentication/idp/single-sign-on"]'

