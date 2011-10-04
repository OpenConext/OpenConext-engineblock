Feature: Signature Validation
  In order to make sure that authentication is secure
  as an end user
  I want my messages to be signed and validated, the signature must be valid

  Background:
    Given we are using EngineBlock on the "test" environment
      And we have a "https://portal.test.surfconext.nl/shibboleth" SP configured
      And we have a SURFguest user with the username "test-boy", name "Boy" and password "test-boy"
      And we have a WrongCertIdP user with the username "user", name "User" and password "password"
      And we have configured a "https://wrongcertsp.dev.surfconext.nl/simplesaml/module.php/saml/sp/metadata.php/wrong-cert-sp" SP that uses a wrong signing certificate
      And we have configured an "https://wrongcertidp.dev.surfconext.nl/simplesaml/saml2/idp/metadata.php" IdP that uses a wrong signing certificate

  Scenario: Boy fails to log in at the Wrong Cert SP
    When I go to the Wrong Cert SP
      And at the Wrong Cert SP I select "https://engine.test.surfconext.nl/authentication/idp/metadata"
    Then EngineBlock directly gives me the error "Error - An error occured.."

  Scenario: User Fails to log in on the Portal SP using the Wrong Cert IdP
    When I go the Portal with "https://wrongcertidp.dev.surfconext.nl/simplesaml/saml2/idp/metadata.php" as the entity ID
      And I log in to WrongCertIdp as "user" with password "password"
    Then EngineBlock gives me the error "Error - An error occured.."