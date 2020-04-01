var fs = require('fs');
const {toMatchImageSnapshot} = require('jest-image-snapshot');

// Extend Jest expect
expect.extend({toMatchImageSnapshot});

export default class ScreenshotTester {

    constructor() {
        this.viewports = [
            [375, 667],
            [1920, 1080],
        ];
        this.tests = [];

        // Use global variables, to prevent outerscope npm lint warning
        this.global = global;
        this.expect = expect;
    }

    runAll(which, waitFor, tests) {
        this.build(which, waitFor, tests);
        this.createDir(which);
        this.run();
    }

    build(which, waitFor, tests) {
        this.tests = [];
        tests.forEach((test) => {
            this.viewports.forEach((value) => {
                this.tests.push({
                    which: which,
                    waitFor: waitFor,
                    name: test[0],
                    url: test[1],
                    width: value[0],
                    height: value[1],
                });
            });
        });
    }

    run() {
        for (const t of this.tests) {
            it(`${t.name}-${t.width}x${t.height}`, async () => {

                let page = await this.global.__BROWSER__.newPage();

                await page.setViewport({
                    width: t.width,
                    height: t.height,
                });
                await page.goto(t.url, 2000);
                await page.waitFor(t.waitFor, 2000);

                const screenshot = await page.screenshot({
                    fullPage: true,
                    path: `./material/javascripts/tests/visual-regression/screenshots/${t.which}/${t.name}-${t.width}x${t.height}.png`
                });

                await page.close();

                this.expect(screenshot).toMatchImageSnapshot();
            }, 5000);
        }
    }

    createDir(which) {
        const dir = `./material/javascripts/tests/visual-regression/screenshots/${which}`;
        if (!fs.existsSync(dir)){
            fs.mkdirSync(dir);
        }
    }
}
