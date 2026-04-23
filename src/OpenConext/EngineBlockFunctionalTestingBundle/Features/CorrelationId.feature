Feature:
  In order to trace a complete authentication flow across log entries
  As a SURF operator
  I need a single correlation_id to appear in every log record belonging to the same SAML flow

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
    And no registered SPs
    And no registered Idps
    And a Service Provider named "CorrId-SP"

  Scenario: A user authenticating via the WAYF completes the full four-leg flow
    Given an Identity Provider named "CorrId-IdP-A"
    And an Identity Provider named "CorrId-IdP-B"
    When I log in at "CorrId-SP"
    And I select "CorrId-IdP-A" on the WAYF
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/CorrId-SP/acs"
    #And I dump the log records
    And the following log messages should have a correlation_id:
      | message                                                                                                   |
      | Multiple candidate IdPs: redirecting to WAYF                                                              |
      | Done calling service 'singleSignOnService'                                                                |
      | Done calling service 'continueToIdp'                                                                      |
      | /Received Assertion from Issuer .*/                                                                       |
      | /SP is not configured for MFA for IdP, or for transparant AuthnContext, skipping validation .*/           |
      | Verifying if schacHomeOrganization is allowed by configured IdP shibmd:scopes                             |
      | No shibmd:scope found in the IdP metadata, not verifying schacHomeOrganization                            |
      | Verifying if eduPersonPrincipalName is allowed by configured IdP shibmd:scopes                            |
      | No shibmd:scope found in the IdP metadata, not verifying eduPersonPrincipalName                           |
      | Verifying if subject-id is allowed by configured IdP shibmd:scopes                                        |
      | No shibmd:scope found in the IdP metadata, not verifying subject-id                                       |
      | /No Attribute Aggregation for .*/                                                                         |
      | /No SBS interrupt for serviceProvider.*/                                                                  |
      | StepupDecision: determine highest LoA                                                                     |
      | StepupDecision: no level set, no Stepup required                                                          |
      | Handle Consent authentication callout                                                                     |
      | Using internal binding for destination /authentication/idp/provide-consent                                |
      | Calling service 'provideConsentService'                                                                   |
      | Done calling service 'provideConsentService'                                                              |
      | Done calling service 'assertionConsumerService'                                                           |
      | /Using internal binding for destination https:\/\/engine.dev.openconext.local\/authenticati.*/            |
      | Calling service 'processedAssertionConsumerService'                                                       |
      | /No ARP available for https:\/\/engine.dev.openconext.local\/functional-testing\/CorrId-SP\/metadata. .*/ |
      | Executing the ApplyTrustedProxyBehavior output filter                                                     |
      | Executing the AddIdentityAttributes output filter                                                         |
      | Resolving a persistent nameId                                                                             |
      | Setting the NameId on the Assertion                                                                       |
      | Adding the EduPersonTargetedId on the Assertion                                                           |
      | /Attribute Denormalization: Adding alias 'urn:oid:0.9.2342.19200300.100.1.1' .*/                          |
      | /Attribute Denormalization: Adding alias 'urn:oid:1.3.6.1.4.1.25178.1.2.9' for .*/                        |
      | /Attribute Denormalization: Adding alias 'urn:oid:1.3.6.1.4.1.5923.1.1.1.10' for .*/                      |
      | HTTP-Post: Sending Message                                                                                |
      | Done calling service 'processedAssertionConsumerService'                                                  |
      | Done calling service 'processConsentService'                                                              |

  Scenario: A user authenticating without the WAYF completes the full flow
    Given an Identity Provider named "CorrId-IdP-Only"
    When I log in at "CorrId-SP"
    And I pass through EngineBlock
    And I pass through the IdP
    And I give my consent
    And I pass through EngineBlock
    Then the url should match "functional-testing/CorrId-SP/acs"
    And I dump the log records
    And the following log messages should have a correlation_id:
      | message                                                                                              |
      | HTTP-Post: Sending Message                                                                           |
      | Done calling service 'singleSignOnService'                                                           |
      | /SP is not configured for MFA for IdP, or for transparant AuthnContext, skipping validation of .*/   |
      | Verifying if schacHomeOrganization is allowed by configured IdP shibmd:scopes                        |
      | No shibmd:scope found in the IdP metadata, not verifying schacHomeOrganization                       |
      | Verifying if eduPersonPrincipalName is allowed by configured IdP shibmd:scopes                       |
      | No shibmd:scope found in the IdP metadata, not verifying eduPersonPrincipalName                      |
      | Verifying if subject-id is allowed by configured IdP shibmd:scopes                                   |
      | No shibmd:scope found in the IdP metadata, not verifying subject-id                                  |
      | /No Attribute Aggregation for https:\/\/engine.dev.openconext.local\/functional-testing\/CorrId-S.*/ |
      | /No SBS interrupt for serviceProvider: https:\/\/engine.dev.openconext.local\/functional-testin.*/   |
      | StepupDecision: determine highest LoA                                                                |
      | StepupDecision: no level set, no Stepup required                                                     |
      | Handle Consent authentication callout                                                                |
      | Using internal binding for destination /authentication/idp/provide-consent                           |
      | Calling service 'provideConsentService'                                                              |
      | Done calling service 'provideConsentService'                                                         |
      | Done calling service 'assertionConsumerService'                                                      |
      | /Using internal binding for destination.*/                                                           |
      | Calling service 'processedAssertionConsumerService'                                                  |
      | /No ARP available for https:\/\/engine.dev.openconext.local\/functional-testing\/CorrId-SP\/metadata. */  |
      | Executing the ApplyTrustedProxyBehavior output filter                                                |
      | Executing the AddIdentityAttributes output filter                                                    |
      | Resolving a persistent nameId                                                                        |
      | Setting the NameId on the Assertion                                                                  |
      | Adding the EduPersonTargetedId on the Assertion                                                      |
      | /Attribute Denormalization: Adding alias 'urn:oid:0.9.2342.19200300.100.1.1'*/                       |
      | /Attribute Denormalization: Adding alias 'urn:oid:1.3.6.1.4.1.25178.1.2.9' f*/                       |
      | /Attribute Denormalization: Adding alias 'urn:oid:1.3.6.1.4.1.5923.1.1.1.10' */                      |
      | login granted                                                                                        |
      | HTTP-Post: Sending Message                                                                           |
      | Done calling service 'processedAssertionConsumerService'                                             |
      | Done calling service 'processConsentService'                                                         |

  @functional
  Scenario: Two concurrent authentication flows each complete independently
    Given an Identity Provider named "CorrId-IdP-A"
    And an Identity Provider named "CorrId-IdP-B"
    When I open 2 browser tabs identified by "Tab-A, Tab-B"
    And I switch to "Tab-A"
    And I log in at "CorrId-SP"
    And I select "CorrId-IdP-A" on the WAYF
    And I switch to "Tab-B"
    And I log in at "CorrId-SP"
    And I select "CorrId-IdP-B" on the WAYF
    And I pass through the IdP
    And I give my consent
    Then the url should match "functional-testing/CorrId-SP/acs"
    And I switch to "Tab-A"
    And I pass through the IdP
    And I give my consent
    Then the url should match "functional-testing/CorrId-SP/acs"
