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
     And the response should match xpath '//mdui:*[@xml:lang="en" and text()="OpenConext Engine"]'
     # Verify the signature method is set to sha256
     And the response should match xpath '//ds:SignatureMethod[@Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"]'
