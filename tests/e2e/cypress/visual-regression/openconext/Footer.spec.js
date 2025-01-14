const viewports = [
    {width: 375, height: 667},
    {width: 1920, height: 1080},
];

const pageTests = [
    {   title: 'all-buttons-visible',
        url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unable-to-receive-message&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","IdentityProvider":"https://mock-idp"}'
    }, {
        title: 'only-support-email-hidden',
        url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=unable-to-receive-message'
    }, {
        title: 'only-wiki-hidden',
        url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=missing-required-fields&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","IdentityProvider":"https://mock-idp"}'
    }, {
        title: 'support-email-and-wiki-button-hidden',
        url: 'https://engine.dev.openconext.local/functional-testing/feedback?template=missing-required-fields'
    },
];

context('Footer', () => {
    viewports.forEach((viewport) => {
        pageTests.forEach((pageDetails) => {
            it('render ' + pageDetails.title + ' ' + viewport.width + 'x' + viewport.height, () => {
                cy.matchImageSnapshots(viewport, pageDetails);
            });
        });
    });
});
