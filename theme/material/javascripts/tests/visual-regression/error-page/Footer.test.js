const timeout = 15000;
const {toMatchImageSnapshot} = require('jest-image-snapshot');

// Extend Jest expect
expect.extend({toMatchImageSnapshot});

const footerDifferences = [
    {
        name: 'all-buttons-visible',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"http://mock-idp"}'
    },
    {
        name: 'only-support-email-hidden',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message'
    },
    {
        name: 'only-wiki-hidden',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=missing-required-fields&feedback-info={"requestId":"5cb4bd3879b49","artCode":"31914", "ipAddress":"192.168.66.98","serviceProvider":"https://current-sp.entity-id.org/metadata", "serviceProviderName": "OpenConext Drop Supplies SP","identityProvider":"http://mock-idp"}'
    },
    {
        name: 'support-email-and-wiki-button-hidden',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=missing-required-fields'
    },
];

const viewports = [
    {width: 375, height: 667},
    {width: 1920, height: 1080},
];

describe(
    'Verify',
    () => {
        let page;

        beforeAll(async () => {
            page = await global.__BROWSER__.newPage();
            jest.setTimeout(20000);
        }, timeout);

        for (const footer of footerDifferences) {
            for (const viewport of viewports) {
                it(`${footer.name}-${viewport.width}x${viewport.height}`, async (page, expect) => {

                    await page.goto(footer.url);
                    await page.setViewport(viewport);
                    await page.waitFor(".error-container");
                    const screenshot = await page.screenshot({
                        fullPage: true,
                        path: `./material/javascripts/tests/visual-regression/error-page/screenshots/footer/${footer.name}-${viewport.width}x${viewport.height}.png`
                    });
                    expect(screenshot).toMatchImageSnapshot();
                });
            }
        }
    },
    timeout,
);
