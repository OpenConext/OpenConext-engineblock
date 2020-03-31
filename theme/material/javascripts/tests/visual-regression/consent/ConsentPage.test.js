const timeout = 5000;
const {toMatchImageSnapshot} = require('jest-image-snapshot');

// Extend Jest expect
expect.extend({toMatchImageSnapshot});

const pageTests = [
    [
        'consent',
        'https://engine.vm.openconext.org/functional-testing/consent',
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
            await page.waitFor(".mod-content", 2000);

            const screenshot = await page.screenshot({
                fullPage: true,
                path: `./material/javascripts/tests/visual-regression/consent/screenshots/consent/${name}-${viewport.width}x${viewport.height}.png`
            });

            await page.close();

            expect(screenshot).toMatchImageSnapshot();
        }, timeout);
    }
});
