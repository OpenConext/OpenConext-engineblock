import expectPuppeteer from 'expect-puppeteer'

const timeout = 5000;

describe(
    'WAYF is showing connected IdPs by default',
    () => {
        let page;
        beforeAll(async () => {
            page = await global.__BROWSER__.newPage();
            const override = Object.assign(page.viewport(), {width: 1920, height: 1080});
            page.setViewport(override);
        }, timeout);

        it('Should show five connected IdPs and the search field', async () => {

            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf');

            // Load the connected IdPs by selecting their h3 titles
            let idps = await page.evaluate(() => [...document.querySelectorAll('#connected-idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(5);

            // Verify the IdP is set with a title
            expect(idps[2]).toEqual('Connected IdP 3');

            // Test the search option by filtering on IdP 4, should yield one search result
            await page.type('.mod-search-input', 'IdP 4');

            // Ensure we have the search field
            const searchFieldValue = await page.evaluate(() => document.querySelector('.mod-search-input').attributes.getNamedItem('placeholder').value);
            expect(searchFieldValue).toEqual('Search for an organisation...');

            // After filtering the search results, verify one result is visible
            idps = await page.evaluate(() => [...document.querySelectorAll('#connected-idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(1);
            expect(idps[0]).toEqual('Connected IdP 4');

            // Ensure some elements are on the page
            await expectPuppeteer(page).toMatch('Select an organisation to login to the service');
            // Ensure some elements are NOT on the page
            await expectPuppeteer(page).not.toMatch('Identity providers without access');
            await expectPuppeteer(page).not.toMatch('Remember my choice');
            await expectPuppeteer(page).not.toMatch('Return to service provider');

            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected.png'});
        });

        it('Should show ten connected IdPs', async () => {
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=10');
            let idps = await page.evaluate(() => [...document.querySelectorAll('#connected-idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(10);
            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-10.png'});
        });

        it('Should show no connected IdPs when cutoff point is configured', async () => {
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=6&cutoffPointForShowingUnfilteredIdps=5');

            let idps = await page.evaluate(() => [...document.querySelectorAll('#connected-idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(0);
            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-cutoff-1.png'});

            await page.type('.mod-search-input', 'IdP');
            idps = await page.evaluate(() => [...document.querySelectorAll('#connected-idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(6);
            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-cutoff-2.png'});
        });

        it('Should show the return to service link when configured', async () => {
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5&backLink=true');
            // Ensure some elements are on the page
            await expectPuppeteer(page).toMatch('Select an organisation to login to the service');
            await expectPuppeteer(page).toMatch('Return to service provider');
            // Ensure some elements are NOT on the page
            await expectPuppeteer(page).not.toMatch('Identity providers without access');
            await expectPuppeteer(page).not.toMatch('Remember my choice');

            // To be more precise, the links should be in the header and footer
            const headerLink = await expect(page).toMatchElement('.mod-header .comp-links li:nth-child(1) a');
            await expectPuppeteer(headerLink).toMatch('Return to service provider');

            const footerLink = await expect(page).toMatchElement('.footer-menu .comp-links li:nth-child(2) a');
            await expectPuppeteer(footerLink).toMatch('Return to service provider');

            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-back-link.png'});
        });

        it('Should show the remember my choice option', async () => {
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true');
            // Ensure some elements are on the page
            await expectPuppeteer(page).toMatch('Select an organisation to login to the service');
            await expectPuppeteer(page).toMatch('Remember my choice');
            // Ensure some elements are NOT on the page
            await expectPuppeteer(page).not.toMatch('Identity providers without access');
            await expectPuppeteer(page).not.toMatch('Return to service provider');

            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-remember-choice.png'});
        });
    },
    timeout,
);
