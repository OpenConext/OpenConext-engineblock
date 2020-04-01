import ScreenshotTester from "./helper/ScreenshotTester";

const pageTests = [
    [   'all-buttons-visible',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"http://mock-idp"}'
    ],[
        'only-support-email-hidden',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message'
    ],[
        'only-wiki-hidden',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=missing-required-fields&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"http://mock-idp"}'
    ],[
        'support-email-and-wiki-button-hidden',
        'https://engine.vm.openconext.org/functional-testing/feedback?template=missing-required-fields'
    ],
];

describe('Verify error page footer', () => {
    const tester = new ScreenshotTester();
    tester.runAll('footer', '.error-container', pageTests);
});
