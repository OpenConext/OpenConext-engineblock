Feature:
  In order to trace a complete authentication flow across log entries
  As a SURF operator
  I need a single correlation_id to appear in every log record belonging to the same SAML flow

  Background:
    Given an EngineBlock instance on "dev.openconext.local"
      And no registered SPs
      And no registered Idps
      And a Service Provider named "CorrId-SP"

  # ── WAYF path ──────────────────────────────────────────────────────────────
  # Two IdPs are registered, so the WAYF is shown after the initial SSO request.
  # The correlation ID is minted in SingleSignOn.serve(), propagated to
  # ContinueToIdp (user picks an IdP), then forwarded to the IdP request via
  # link(), and finally picked up in AssertionConsumer and ProvideConsent/
  # ProcessConsent.  A complete round-trip through all four HTTP legs must
  # succeed without error.
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

  # ── Direct path (no WAYF) ───────────────────────────────────────────────────
  # When only one IdP is available the WAYF is skipped; the correlation ID is
  # minted inside ProxyServer.sendAuthenticationRequest() and linked to the IdP
  # request.  AssertionConsumer and consent legs must resolve it from the IdP
  # request ID stored in InResponseTo.
  Scenario: A user authenticating without the WAYF completes the full flow
    Given an Identity Provider named "CorrId-IdP-Only"
    When I log in at "CorrId-SP"
     And I pass through EngineBlock
     And I pass through the IdP
     And I give my consent
     And I pass through EngineBlock
    Then the url should match "functional-testing/CorrId-SP/acs"

  # ── Concurrent flows ────────────────────────────────────────────────────────
  # Two simultaneous authentications in separate browser tabs share the same PHP
  # session.  Each flow must mint its own correlation ID and the two IDs must
  # not bleed into each other.  Both flows must complete successfully and land
  # on the correct SP ACS URL.
  # Requires the @functional tag to use the Chrome driver (browser tabs need JS).
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
