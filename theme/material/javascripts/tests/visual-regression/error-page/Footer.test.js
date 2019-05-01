const timeout = 15000;
const {toMatchImageSnapshot} = require('jest-image-snapshot');

// Extend Jest expect
expect.extend({toMatchImageSnapshot});

const footerDifferences = [
    {
        name: 'all-buttons-visible',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message&parameters={"supportEmail":"support@openconext.nl","showWikiButton":true}'
    },
    {
        name: 'only-support-email-hidden',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message&parameters={"showWikiButton":true}'
    },
    {
        name: 'only-wiki-hidden',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message&parameters={"supportEmail":"support@openconext.nl"}'
    },
    {
        name: 'support-email-and-wiki-button-hidden',
        url: 'https://engine.vm.openconext.org/functional-testing/feedback?template=unable-to-receive-message&parameters={}'
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
                it(`${footer.name}-${viewport.width}x${viewport.height}`, async () => {

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
