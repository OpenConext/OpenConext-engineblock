Feature:
  In order to explain my login problem's to the helpdesk
  As a user
  I need to see useful error information when something goes wrong

  Scenario: Engineblock shows useful status code and message when Idp returns an error code
    Given Dummy Idp is configured to use the "ErrorStatusCode" testcase
    When I go to "https://engine-test.demo.openconext.org/dummy/sp"
    And I press "Dummy Idp"
    And I press "Continue"
    Then I should see "Idp error"
    And I should see "Status Code: urn:oasis:names:tc:SAML:2.0:status:InvalidNameIDPolicy"
    And I should see "Status Message: NameIdPolicy is invalid"

  Scenario: Engineblock shows useful message when The IdP sends an invalid response
    Given Dummy Idp is configured to use the "PrivateKeyRollover" testcase
    When I go to "https://engine-test.demo.openconext.org/dummy/sp"
    And I press "Dummy Idp"
    And I press "Continue"
    Then I should see "Invalid Idp response"

  Scenario: Engineblock shows useful message when no idps are configured for the given sp
    When I go to "https://engine-test.demo.openconext.org/dummy/sp?nr=2"
    Then I should see "No Identity Providers found"

  #@todo test:
    # - The assertion is not valid yet. This happens when the clock on the IdP is running ahead.
    # - We cannot locate the session identifier of the user. This happens when: a user is directed to another LB or we loose their session info for some other reason.
    # - The ACL does not allow a user to access the service: This happens with SPs the use our transparent (idps) metadata and send an AuthnRequest for an IdP this is not allowed access to the SP.
    # - The user sent us a SAML assertion, but did not send the session cookie (so we cannot locate their session). This happens e.g. with login in an iframe with third party cookies disabled or other situations where the security settings in a browser prevent a cookie from being sent.