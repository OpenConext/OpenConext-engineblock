const timeout = 5000;
const {toMatchImageSnapshot} = require('jest-image-snapshot');

// Extend Jest expect
expect.extend({toMatchImageSnapshot});

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

const viewports = [
    {width: 375, height: 667},
    {width: 1920, height: 1080},
];

describe.each(pageTests)('Verify (%s)', (name, url) => {
    for (const viewport of viewports) {
        it(`${name}-${viewport.width}x${viewport.height}`, async () => {
            let page = await global.__BROWSER__.newPage();
            await page.setViewport(viewport);
            await page.goto(url, 2000);
            await page.waitFor(".error-container", 2000);

            const screenshot = await page.screenshot({
                fullPage: true,
                path: `./material/javascripts/tests/visual-regression/error-page/screenshots/footer/${name}-${viewport.width}x${viewport.height}.png`
            });

            await page.close();

            expect(screenshot).toMatchImageSnapshot();
        }, timeout);
    }
});
