@selenium
Feature:
    In order to offer a stable and user friendly service
    As EngineBlock
    I want to support multiple in-flight AuthNRequests

    Background:
        Given an EngineBlock instance on "dev.openconext.local"
        And no registered SPs
        And no registered Idps
        And an Identity Provider named "SSO-IdP"
        And a Service Provider named "SSO-SP"
        And a Service Provider named "SSO-Two"
        And I open 2 browser tabs identified by "Browser tab 1, Browser tab 2"

    Scenario: Two solicited authentication requests sequential
       When I switch to "Browser tab 1"
        And I log in at "SSO-SP"
        And I pass through the SP
        And I give my consent
       Then the url should match "functional-testing/SSO-SP/acs"
        And I switch to "Browser tab 2"
        And I log in at "SSO-Two"
        And I pass through the SP
        And I give my consent
       Then the url should match "functional-testing/SSO-Two/acs"

    Scenario: Two solicited authentication requests mixed
       When I switch to "Browser tab 1"
        And I log in at "SSO-SP"
        And I pass through the SP
        And I switch to "Browser tab 2"
        And I log in at "SSO-Two"
        And I pass through the SP
        And I give my consent
       Then the url should match "functional-testing/SSO-Two/acs"
        And I switch to "Browser tab 1"
        And I give my consent
       Then the url should match "functional-testing/SSO-SP/acs"

    Scenario: One solicited and one unsolicited authentication requests
       When I switch to "Browser tab 1"
        And I log in at "SSO-SP"
        And I pass through the SP
        And I switch to "Browser tab 2"
        And An IdP initiated Single Sign on for SP "SSO-Two" is triggered by IdP "SSO-IdP"
        And I pass through the IdP
        And I switch to "Browser tab 1"
        And I give my consent
       Then the url should match "functional-testing/SSO-SP/acs"
        And I switch to "Browser tab 2"
        And I give my consent
        Then the url should match "functional-testing/SSO-Two/acs"
