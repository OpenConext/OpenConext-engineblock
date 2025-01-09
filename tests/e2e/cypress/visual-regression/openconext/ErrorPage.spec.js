const viewports = [
  {width: 375, height: 667},
  {width: 1920, height: 1080},
];

const pageTests = [
  {
    title: 'unable-to-receive-message',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unable-to-receive-message&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98", "serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP"}'
  }, {
    title: 'session-lost',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=session-lost'
  }, {
    title: 'session-not-started',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=session-not-started'
  }, {
    title: 'no-idps',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=no-idps&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName":"OpenConext Drop Supplies SP"}'
  }, {
    title: 'no-idps-sp-proxy',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=no-idps&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914","ipAddress":"192.168.66.98","currentServiceProvider":"https://current-sp.entity-id.org/proxy-metadata","serviceProvider":"https://current-sp.entity-id.org/proxy-metadata","serviceProviderName":"OpenConext Drop Supplies SP","proxyServiceProvider":"https://current-sp.entity-id.org/metadata"}'
  }, {
    title: 'invalid-acs-location',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=invalid-acs-location&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName":"OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'unsupported-acs-location-scheme',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unsupported-acs-location-scheme&feedback-info={"statusCode": "418", "statusMessage": "(No message provided)", "requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'missing-required-fields',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=missing-required-fields&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98", "serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'invalid-acs-binding',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=invalid-acs-binding&feedback-info={"serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'received-error-status-code',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=received-error-status-code'
  }, {
    title: 'received-invalid-signed-response',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=received-invalid-signed-response&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'received-invalid-response',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=received-invalid-response&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'unknown_requesterid_in_authnrequest',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unknown-requesterid-in-authnrequest&feedback-info={"Idp Hash": "64531cc179d0d2e66243c30e58125f0a", "requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP"}'
  }, {
    title: 'unknown-preselected-idp',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unknown-preselected-idp&feedback-info={"Idp Hash": "64531cc179d0d2e66243c30e58125f0a", "requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP"}'
  }, {
    title: 'stuck-in-authentication-loop',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=stuck-in-authentication-loop&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'clock-issue',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=clock-issue&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'unsupported-signature-method',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unsupported-signature-method&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP"}&parameters={"signatureMethod":"https://www.w3.org/2000/09/xmldsig%23rsa-sha1"}'
  }, {
    title: 'unknown-service-provider',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unknown-service-provider&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP"}&parameters={"entityId":"https://serviceregistry.dev.openconext.local/simplesaml/module.php/saml/sp/metadata.php/default-sp"}'
  }, {
    title: 'unknown-identity-provider',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unknown-identity-provider&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","IdentityProvider":"https://current-idp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP"}&parameters={"entityId":"https://serviceregistry.dev.openconext.local/simplesaml/module.php/saml/idp/metadata.php/default-idp"}'
  }, {
    title: 'invalid-attribute-value',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=invalid-attribute-value&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}&parameters={"attributeName":"schacHomeOrganization","attributeValue":"openconext"}'
  }, {
    title: 'no-authentication-request-received',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=no-authentication-request-received&parameters={"message":"No SAMLRequest parameter was found in the HTTP POST request parameters"}'
  }, {
    title: 'authorization-policy-violation',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=authorization-policy-violation&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}&parameters={"logo":{"height":"96","width":"96","url":"https://static.dev.openconext.local/media/conext_logo.png"},"policyDecisionMessage":"No localized deny messages present"}'
   }, {
     title: 'uncaught-error',
     url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unknown-error&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'authn-context-class-ref-blacklisted',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=authn-context-class-ref-blacklisted&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}'
  }, {
    title: 'stepup-callout-unmet-loa',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=stepup-callout-unmet-loa&feedback-info=%7B%22statusCode%22%3A%22Responder%2FAuthnFailed%22%2C%22statusMessage%22%3A%22Authentication+cancelled+by+user%22%2C%22requestId%22%3A%225cb4bd3879b49%22%2C%22ipAddress%22%3A%22192.168.66.98%22%2C%22artCode%22%3A%2231914%22%7D&lang=en'
  }, {
    title: 'stepup-callout-user-cancelled',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=stepup-callout-user-cancelled&feedback-info=%7B%22statusCode%22%3A%22Responder%2FAuthnFailed%22%2C%22statusMessage%22%3A%22Authentication+cancelled+by+user%22%2C%22requestId%22%3A%225cb4bd3879b49%22%2C%22ipAddress%22%3A%22192.168.66.98%22%2C%22artCode%22%3A%2231914%22%7D&lang=en'
  }, {
    title: 'stepup-callout-unknown',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=stepup-callout-unknown&feedback-info=%7B%22statusCode%22%3A%22Responder%2FAuthnFailed%22%2C%22statusMessage%22%3A%22Authentication+cancelled+by+user%22%2C%22requestId%22%3A%225cb4bd3879b49%22%2C%22ipAddress%22%3A%22192.168.66.98%22%2C%22artCode%22%3A%2231914%22%7D&lang=en'
  }, {
    title: 'metadata-entity-id-not-found',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=metadata-entity-not-found&parameters={"message":"Could not find your entity"}&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98"}'
  }, {
    title: 'invalid-mfa-authn-context-class-ref',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=invalid-mfa-authn-context-class-ref&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}',
  }, {
    title: '',
    url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=invalid-mfa-authn-context-class-ref&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","IdentityProvider":"https://current-idp.entity-id.org/metadata"}',
  }
];

context('Error', () => {
  viewports.forEach((viewport) => {
    pageTests.forEach((pageDetails) => {
      it('render ' + pageDetails.title + ' ' + viewport.width + 'x' + viewport.height, () => {
        cy.matchImageSnapshots(viewport, pageDetails);
      });
    });
  });
});
