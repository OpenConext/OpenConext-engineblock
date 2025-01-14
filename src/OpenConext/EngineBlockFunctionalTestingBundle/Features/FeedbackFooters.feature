Feature:
  In order to present an end-user on an error page with additional info
  As a user
  I need to see useful footer blocks when something goes wrong

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And an Identity Provider named "Dummy Idp"
    And a Service Provider named "Dummy SP"

  Scenario: When a wiki link is configured in a translation the wiki link should be visible
   Given I have configured the following translations:
        | Key                                                                 | Value                          |
        | error_feedback_wiki_links_feedback_unknown_error                    | https://support.openconext.org |
    When I go to Engineblock URL "/feedback/unknown-error"
    Then I should see "Error - An error occurred"
     And I should see "OpenConext Wiki"
     And I should see "Service desk"
     And I should not see "support@openconext.org"

  Scenario: When a wiki link is not configured in a translation the wiki link should be hidden
    Given I have configured the following translations:
      | Key                                                                 | Value                          |
      | error_feedback_wiki_links_feedback_unknown_error                    |                                |
    When I go to Engineblock URL "/feedback/unknown-error"
    Then I should see "Error - An error occurred"
      And I should not see "OpenConext Wiki"
      And I should see "Service desk"
      And I should not see "support@openconext.org"


  Scenario: When a IdP specific error page is shown and a translation is configured the support emailaddress of the IdP should be visible
    Given The clock on the IdP "Dummy Idp" is ahead
    And I have configured the following translations:
      | Key                                                                                       | Value                          |
      | error_feedback_idp_contact_label_small_authentication_feedback_response_clock_issue       | MAIL                           |
    When I log in at "Dummy SP"
      And I pass through EngineBlock
      And I pass through the IdP
      And I give my consent
      And I should see "OpenConext Wiki"
      And I should see "support@openconext.org"


  Scenario: When a IdP specific error page is shown and a translation is not configured the support emailaddress of the IdP should be hidden
    Given The clock on the IdP "Dummy Idp" is ahead
    And I have configured the following translations:
      | Key                                                                                       | Value                          |
      | error_feedback_idp_contact_label_small_authentication_feedback_response_clock_issue       |                                |
    When I log in at "Dummy SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I should see "OpenConext Wiki"
    And I should not see "support@openconext.org"
