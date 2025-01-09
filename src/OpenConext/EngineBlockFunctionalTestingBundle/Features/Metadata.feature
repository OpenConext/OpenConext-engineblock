Feature:
  In order to ensure EngineBlock interoperability
  As an user
  I want to be able to share EngineBlock metadata

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps

  Scenario: A user can request the EngineBlock SP Proxy metadata
    When I go to Engineblock URL "/authentication/sp/metadata"
    # Verify the entity id is correctly set in the metadata
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/authentication/sp/metadata"]'
    # Verify the display name (EN) correctly set in the metadata
      And the response should match xpath '//mdui:DisplayName[@xml:lang="en" and text()="OpenConext EngineBlock"]'
      # Verify the signature method is set to sha256
      And the response should match xpath '//ds:SignatureMethod[@Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"]'
      # Verify the ACS location and binding
      And the response should match xpath '//md:AssertionConsumerService[@Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" and @Location="https://engine.dev.openconext.local/authentication/sp/consume-assertion"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  Scenario: A user can request the EngineBlock IdP Proxy metadata
    When I go to Engineblock URL "/authentication/idp/metadata"
    # Verify the entity id is correctly set in the metadata
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/authentication/idp/metadata"]'
      # Verify the display name (EN) correctly set in the metadata
      And the response should match xpath '//mdui:DisplayName[@xml:lang="en" and text()="OpenConext EngineBlock"]'
      # Verify the signature method is set to sha256
      And the response should match xpath '//ds:SignatureMethod[@Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"]'
      # Verify SSO location and binding is set correctly
      And the response should match xpath '//md:SingleSignOnService[@Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" and @Location="https://engine.dev.openconext.local/authentication/idp/single-sign-on"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  Scenario: A user can request the EngineBlock stepup metadata
    When I go to Engineblock URL "/authentication/stepup/metadata"
    # Verify the entity id is correctly set in the metadata
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/authentication/stepup/metadata"]'
      # Verify the signature method is set to sha256
      And the response should match xpath '//ds:SignatureMethod[@Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"]'
      # Verify the ACS location and binding
      And the response should match xpath '//md:AssertionConsumerService[@Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" and @Location="https://engine.dev.openconext.local/authentication/stepup/consume-assertion"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  Scenario: A user can request the metadata of all known and visible IdPs
    Given an Identity Provider named "Known-IdP"
      And an Identity Provider named "Second-IdP"
      And an Identity Provider named "Regular-IdP"
    When I go to Engineblock URL "/authentication/proxy/idps-metadata"
    # Verify the three IdPs are present in the list
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Known-IdP/metadata"]'
      And the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Second-IdP/metadata"]'
      And the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Regular-IdP/metadata"]'
      # And Engine IdP is not listed
      And the response should not match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/authentication/idp/metadata"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the schema and hostname are not appende twice as was done prior to resolving: https://www.pivotaltracker.com/story/show/169724838
      And the response should not match xpath '//mdui:Logo[text()="https://engine.dev.openconext.localhttps://engine.dev.openconext.local/images/logo.png"]'

  Scenario: A user can request the metadata and does not see invisible IdPs
    Given an Identity Provider named "Known-IdP"
      And an Identity Provider named "Second-IdP"
      And an Identity Provider named "Hidden-IdP"
      And the IdP "Hidden-IdP" is hidden
    When I go to Engineblock URL "/authentication/proxy/idps-metadata"
    # Verify the two IdPs are present in the list
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Known-IdP/metadata"]'
      And the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Second-IdP/metadata"]'
      # The Hidden IdP is not listed
      And the response should not match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Hidden-IdP/metadata"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  Scenario: A user can request the metadata and it will contain shibmd:Scope elements
    Given an Identity Provider named "Known-IdP"
      And an Identity Provider named "Second-IdP"
      And the Idp with name "Known-IdP" has shibd scope "foobar.example.com"
    When I go to Engineblock URL "/authentication/proxy/idps-metadata"
    # Verify the two IdPs are present in the list
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Known-IdP/metadata"]'
      And the response should match xpath '//shibmd:Scope[@regexp="false" and text() = "foobar.example.com"]'
      And the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Second-IdP/metadata"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  Scenario: A user can request the metadata of the IdPs connected to a specific SP
    Given an Identity Provider named "Connected-IdP"
      And an Identity Provider named "Second-Connected-IdP"
      And an Identity Provider named "Not-Connected-IdP"
      And a Service Provider named "Test-SP"
      And SP "Test-SP" is not connected to IdP "Not-Connected-IdP"
    When I go to Engineblock URL "/authentication/proxy/idps-metadata?sp-entity-id=https://engine.dev.openconext.local/functional-testing/Test-SP/metadata"
    # Verify the two connected IdPs are present in the list
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Connected-IdP/metadata"]'
      And the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Second-Connected-IdP/metadata"]'
      # Verify the disconnected IdP is not listed
      And the response should not match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Not-Connected-IdP/metadata"]'
      # Verify the SP enitty is not listed (used to be the case in older EB versions)
      And the response should not match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Test-SP/metadata"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  # Perform the same tests, but now for URLs with a specific preselected Key ID

  Scenario: A user can request the EngineBlock SP Proxy metadata with a keyID
    When I go to Engineblock URL "/authentication/sp/metadata/key:default"
    # Verify the entity id is correctly set in the metadata
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/authentication/sp/metadata"]'
    # Verify the display name (EN) correctly set in the metadata
      And the response should match xpath '//mdui:DisplayName[@xml:lang="en" and text()="OpenConext EngineBlock"]'
      # Verify the signature method is set to sha256
      And the response should match xpath '//ds:SignatureMethod[@Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"]'
      # Verify the ACS location and binding
      And the response should match xpath '//md:AssertionConsumerService[@Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" and @Location="https://engine.dev.openconext.local/authentication/sp/consume-assertion"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  Scenario: A user can request the EngineBlock IdP Proxy metadata with a keyID
    When I go to Engineblock URL "/authentication/idp/metadata/key:default"
    # Verify the entity id is correctly set in the metadata
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/authentication/idp/metadata"]'
      # Verify the display name (EN) correctly set in the metadata
      And the response should match xpath '//mdui:DisplayName[@xml:lang="en" and text()="OpenConext EngineBlock"]'
      # Verify the signature method is set to sha256
      And the response should match xpath '//ds:SignatureMethod[@Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"]'
      # Verify SSO location and binding is set correctly including Key ID
      And the response should match xpath '//md:SingleSignOnService[@Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" and @Location="https://engine.dev.openconext.local/authentication/idp/single-sign-on/key:default"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  Scenario: A user can request the EngineBlock stepup metadata with a keyID
    When I go to Engineblock URL "/authentication/stepup/metadata/key:default"
    # Verify the entity id is correctly set in the metadata
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/authentication/stepup/metadata"]'
      # Verify the signature method is set to sha256
      And the response should match xpath '//ds:SignatureMethod[@Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"]'
      # Verify the ACS location and binding
      And the response should match xpath '//md:AssertionConsumerService[@Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" and @Location="https://engine.dev.openconext.local/authentication/stepup/consume-assertion"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  Scenario: A user can request the metadata of all known and visible IdPs with a keyID
    Given an Identity Provider named "Known-IdP"
      And an Identity Provider named "Second-IdP"
      And an Identity Provider named "Regular-IdP"
    When I go to Engineblock URL "/authentication/proxy/idps-metadata/key:default"
    # Verify the three IdPs are present in the list
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Known-IdP/metadata"]'
      And the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Second-IdP/metadata"]'
      And the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Regular-IdP/metadata"]'
      # And Engine IdP is not listed
      And the response should not match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/authentication/idp/metadata"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the schema and hostname are not appende twice as was done prior to resolving: https://www.pivotaltracker.com/story/show/169724838
      And the response should not match xpath '//mdui:Logo[text()="https://engine.dev.openconext.localhttps://engine.dev.openconext.local/images/logo.png"]'

  Scenario: A user can request the metadata of the IdPs connected to a specific SP with a keyID
    Given an Identity Provider named "Connected-IdP"
      And an Identity Provider named "Second-Connected-IdP"
      And an Identity Provider named "Not-Connected-IdP"
      And a Service Provider named "Test-SP"
      And SP "Test-SP" is not connected to IdP "Not-Connected-IdP"
    When I go to Engineblock URL "/authentication/proxy/idps-metadata/key:default?sp-entity-id=https://engine.dev.openconext.local/functional-testing/Test-SP/metadata"
    # Verify the two connected IdPs are present in the list
    Then the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Connected-IdP/metadata"]'
      And the response should match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Second-Connected-IdP/metadata"]'
      # Verify the disconnected IdP is not listed
      And the response should not match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Not-Connected-IdP/metadata"]'
      # Verify the SP enitty is not listed (used to be the case in older EB versions)
      And the response should not match xpath '//md:EntityDescriptor[@entityID="https://engine.dev.openconext.local/functional-testing/Test-SP/metadata"]'
      # Verify the propagated signing key is EB key
      And the response should match xpath '//md:KeyDescriptor[@use="signing"]//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'
      # Verify the used signing key is EB key
      And the response should match xpath '//ds:Signature//ds:X509Certificate[starts-with(.,"MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMT")]'

  Scenario: A user can request the EngineBlock SP public certificate
    When I go to Engineblock URL "/authentication/sp/certificate"
    Then the response should contain '-----BEGIN CERTIFICATE-----'
      And the response should contain 'MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNV'
      And the response should contain '-----END CERTIFICATE-----'

  Scenario: A user can request the EngineBlock SP public certificate for a specific key id
    When I go to Engineblock URL "/authentication/sp/certificate/key:default"
    Then the response should contain '-----BEGIN CERTIFICATE-----'
      And the response should contain 'MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNV'
      And the response should contain '-----END CERTIFICATE-----'

  Scenario: A user can request the EngineBlock IdP public certificate
    When I go to Engineblock URL "/authentication/idp/certificate"
    Then the response should contain '-----BEGIN CERTIFICATE-----'
      And the response should contain 'MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNV'
      And the response should contain '-----END CERTIFICATE-----'

  Scenario: A user can request the EngineBlock IdP public certificate for a specific key id
    When I go to Engineblock URL "/authentication/idp/certificate/key:default"
    Then the response should contain '-----BEGIN CERTIFICATE-----'
      And the response should contain 'MIIDuDCCAqCgAwIBAgIJAPdqJ9JQKN6vMA0GCSqGSIb3DQEBBQUAMEYxDzANBgNV'
      And the response should contain '-----END CERTIFICATE-----'
