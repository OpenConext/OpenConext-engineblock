import ScreenshotTester from "./helper/ScreenshotTester";

const pageTests = [
    [
        'unable-to-receive-message',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98", "serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP"}'
    ],[
        'session-lost',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=session-lost'
    ],[
        'session-not-started',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=session-not-started'
    ],[
        'no-idps',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=no-idps&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName":"OpenConext Drop Supplies SP"}'
    ],[
        'invalid-acs-location',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=invalid-acs-location&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName":"OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'unsupported-acs-location-scheme',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=unsupported-acs-location-scheme&feedback-info={"statusCode": "418", "statusMessage": "(No message provided)", "requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'missing-required-fields',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=missing-required-fields&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98", "serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'invalid-acs-binding',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=invalid-acs-binding&feedback-info={"serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'received-error-status-code',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=received-error-status-code'
    ],[
        'received-invalid-signed-response',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=received-invalid-signed-response&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'received-invalid-response',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=received-invalid-response&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'unknown_requesterid_in_authnrequest',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=unknown-requesterid-in-authnrequest&feedback-info={"Idp Hash": "64531cc179d0d2e66243c30e58125f0a", "requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP"}'
    ],[
        'unknown-preselected-idp',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=unknown-preselected-idp&feedback-info={"Idp Hash": "64531cc179d0d2e66243c30e58125f0a", "requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP"}'
    ],[
        'stuck-in-authentication-loop',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=stuck-in-authentication-loop&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'clock-issue',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=clock-issue&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'unsupported-signature-method',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=unsupported-signature-method&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP"}&parameters={"signatureMethod":"http://www.w3.org/2000/09/xmldsig%23rsa-sha1"}'
    ],[
        'unknown-service-provider',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=unknown-service-provider&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP"}&parameters={"entityId":"https://serviceregistry.vm.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp"}'
    ],[
        'unknown-identity-provider',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=unknown-identity-provider&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","IdentityProvider":"https://current-idp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP"}&parameters={"entityId":"https://serviceregistry.vm.openconext.org/simplesaml/module.php/saml/idp/metadata.php/default-idp"}'
    ],[
        'invalid-attribute-value',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=invalid-attribute-value&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}&parameters={"attributeName":"schacHomeOrganization","attributeValue":"openconext"}'
    ],[
        'no-authentication-request-received',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=no-authentication-request-received&parameters={"message":"No SAMLRequest parameter was found in the HTTP POST request parameters"}'
    ],[
        'authorization-policy-violation',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=authorization-policy-violation&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}&parameters={"logo":{"height":"96","width":"96","url":"https://static.vm.openconext.org/media/conext_logo.png"},"policyDecisionMessage":"No localized deny messages present"}'
    ],[
        'uncaught-error',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=generic&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'authn-context-class-ref-blacklisted',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=authn-context-class-ref-blacklisted&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}'
    ],[
        'stepup-callout-unmet-loa',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=stepup-callout-unmet-loa&feedback-info=%7B%22statusCode%22%3A%22Responder%2FAuthnFailed%22%2C%22statusMessage%22%3A%22Authentication+cancelled+by+user%22%2C%22requestId%22%3A%225cb4bd3879b49%22%2C%22ipAddress%22%3A%22192.168.66.98%22%2C%22artCode%22%3A%2231914%22%7D&lang=en'
    ],[
        'stepup-callout-user-cancelled',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=stepup-callout-user-cancelled&feedback-info=%7B%22statusCode%22%3A%22Responder%2FAuthnFailed%22%2C%22statusMessage%22%3A%22Authentication+cancelled+by+user%22%2C%22requestId%22%3A%225cb4bd3879b49%22%2C%22ipAddress%22%3A%22192.168.66.98%22%2C%22artCode%22%3A%2231914%22%7D&lang=en'
    ],[
        'stepup-callout-unknown',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=stepup-callout-unknown&feedback-info=%7B%22statusCode%22%3A%22Responder%2FAuthnFailed%22%2C%22statusMessage%22%3A%22Authentication+cancelled+by+user%22%2C%22requestId%22%3A%225cb4bd3879b49%22%2C%22ipAddress%22%3A%22192.168.66.98%22%2C%22artCode%22%3A%2231914%22%7D&lang=en'
    ],[
        'metadata-entity-id-not-found',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=metadata-entity-not-found&parameters={"message":"Could not find your entity"}&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98"}'
    ],[
        'invalid-mfa-authn-context-class-ref',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=invalid-mfa-authn-context-class-ref&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata","serviceProviderName":"OpenConext Drop Supplies SP","identityProvider":"https://current-idp.entity-id.org/metadata"}',
    ]
];


describe('Verify error page', () => {
    const tester = new ScreenshotTester();
    tester.runAll('error-page', '.error-container', pageTests);
});
