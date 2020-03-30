const timeout = 15000;
const {toMatchImageSnapshot} = require('jest-image-snapshot');

// Extend Jest expect
expect.extend({toMatchImageSnapshot});

const consentPages = [
    {
        name: 'consent',
        url: 'https://engine.vm.openconext.org/functional-testing/consent"}'
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

        let sets = [];
        for (const consentPage of consentPages) {
            for (const viewport of viewports) {
                sets.push({
                    name: consentPage.name,
                    url: consentPage.url,
                    viewport: viewport,
                    expect: expect,
                    page: page,
                });
            }
        }

        for (const s of sets) {
            it(`${s.name}-${s.viewport.width}x${s.viewport.height}`, async () => {
                await s.page.goto(s.url);
                await s.page.setViewport(s.viewport);
                await s.page.waitFor("tr.toggle-attributes a");
                const screenshot = await s.page.screenshot({
                    fullPage: true,
                    path: `./material/javascripts/tests/visual-regression/consent/screenshots/consent/${s.name}-${s.viewport.width}x${s.viewport.height}.png`
                });
                s.expect(screenshot).toMatchImageSnapshot();
            });
        }
    },
    timeout,
);
