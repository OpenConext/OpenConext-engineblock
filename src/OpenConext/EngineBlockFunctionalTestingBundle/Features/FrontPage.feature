Feature:
  In order to inform users of EngineBlock's capabilities
  As EngineBlock
  I want to display a front page with links to my metadata endpoints and other features

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps

  Scenario: The user can see all available metadata links
    When I go to Engineblock URL "/"
    Then I should see 8 links on the front page
     And I should see text matching "The Public SAML Signing certificate of the OpenConext IdP"
     And I should see URL "/authentication/idp/certificate"
     # The default key should not get a separate URL next to the key-less url
     And I should not see URL "/authentication/idp/certificate/key:default"
     And I should see text matching "The Public SAML metadata \(the entity descriptor\) of the OpenConext IdP Proxy"
     And I should see URL "/authentication/idp/metadata"
     And I should not see URL "/authentication/idp/metadata/key:default"
     And I should see text matching "The Public SAML metadata \(the entities descriptor\) for all the OpenConext IdPs"
     And I should see URL "/authentication/proxy/idps-metadata"
     And I should not see URL "/authentication/proxy/idps-metadata/key:default"
     And I should see text matching "The Public SAML metadata \(the entities descriptor\) of the OpenConext IdPs which"
     And I should see URL "/authentication/proxy/idps-metadata?sp-entity-id=urn:example.org"
     And I should not see URL "/authentication/proxy/idps-metadata/key:default?sp-entity-id=urn:example.org"
     # The test debug link is present on the page
     And I should see text matching "Test authentication with an identity provider."
     And I should see URL "/authentication/sp/debug"
     And I should see text matching "The Public SAML Signing certificate of the OpenConext SP"
     And I should see URL "/authentication/sp/certificate"
     # The key:-variants should only be shown for the IdP metadata feeds
     And I should not see URL "/authentication/sp/certificate/key:"
     And I should see text matching "The Public SAML metadata \(the entity descriptor\) of the OpenConext SP Proxy"
     And I should see URL "/authentication/sp/metadata"
     And I should not see URL "/authentication/sp/metadata/key:"
     And I should see text matching "Step-up authentication Certificate and Metadata"
     And I should see text matching "The Public SAML metadata \(the entity descriptor\) of the OpenConext step-up authentication Proxy"
     And I should see URL "/authentication/stepup/metadata"
     And I should not see URL "/authentication/stepup/metadata/key:"
     # eduGAIN metadata is no longer created by EngineBlock
     And I should not see text matching "eduGAIN"
     # IdP / SP metadata combination (for Shiboleth entities) is no longer supported
     And I should not see text matching "The Public SAML metadata \(the entity descriptor\) of the SURFconext IdP Proxy for SP with entityID"
     And I should not see URL "/authentication/idp/metadata?sp-entity-id=urn:example.org"
     And I should not see URL "/authentication/idp/metadata/key:default?sp-entity-id=urn:example.org"
