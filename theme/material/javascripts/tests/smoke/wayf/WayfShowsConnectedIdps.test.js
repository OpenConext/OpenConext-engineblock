const timeout = 10000;

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
            let idps = await page.evaluate(() => [...document.querySelectorAll('#idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(5);

            // Verify the IdP is set with a title
            expect(idps[2]).toEqual('Connected IdP 3 en');

            // Test the search option by filtering on IdP 4, should yield one search result
            await page.type('.mod-search-input', 'IdP 4');

            // Ensure we have the search field
            const searchFieldValue = await page.evaluate(() => document.querySelector('.mod-search-input').attributes.getNamedItem('placeholder').value);
            expect(searchFieldValue).toEqual('Search for an organisation...');

            // After filtering the search results, verify one result is visible
            idps = await page.evaluate(() => [...document.querySelectorAll('#idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(1);
            expect(idps[0]).toEqual('Connected IdP 4 en');

            const text = await page.evaluate(() => document.body.textContent);

            // Ensure some elements are on the page
            expect(text).toContain('Select an organisation to login to the service');
            // Ensure some elements are NOT on the page
            expect(text).toEqual(expect.not.stringContaining('Identity providers without access'));
            expect(text).toEqual(expect.not.stringContaining('Remember my choice'));
            expect(text).toEqual(expect.not.stringContaining('Return to service provider'));

            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected.png'});
        });

        it('Should show ten connected IdPs', async () => {
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=10');
            let idps = await page.evaluate(() => [...document.querySelectorAll('#idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(10);
            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-10.png'});
        });

        it('Should show no connected IdPs when cutoff point is configured', async () => {
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=6&cutoffPointForShowingUnfilteredIdps=5');

            let idps = await page.evaluate(() => [...document.querySelectorAll('#idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(0);
            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-cutoff-1.png'});

            await page.type('.mod-search-input', 'IdP');
            idps = await page.evaluate(() => [...document.querySelectorAll('#idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(6);
            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-cutoff-2.png'});
        });

        it('Should show the return to service link when configured', async () => {
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5&backLink=true');

            const text = await page.evaluate(() => document.body.textContent);
            // Ensure some elements are on the page
            expect(text).toContain('Select an organisation to login to the service');
            expect(text).toContain('Return to service provider');
            // Ensure some elements are NOT on the page
            expect(text).toEqual(expect.not.stringContaining('Identity providers without access'));
            expect(text).toEqual(expect.not.stringContaining('Remember my choice'));

            // To be more precise, the links should be in the header and footer
            const headerLink = await page.evaluate(() => document.querySelector('.mod-header .comp-links li:nth-child(1) a').textContent);
            await expect(headerLink).toMatch('Return to service provider');

            const footerLink = await page.evaluate(() => document.querySelector('.footer-menu .comp-links li:nth-child(2) a').textContent);
            await expect(footerLink).toMatch('Return to service provider');

            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-back-link.png'});
        });

        it('Should show the remember my choice option', async () => {
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5&rememberChoiceFeature=true');

            const text = await page.evaluate(() => document.body.textContent);
            // Ensure some elements are on the page
            expect(text).toContain('Select an organisation to login to the service');
            expect(text).toContain('Remember my choice');
            // Ensure some elements are NOT on the page
            expect(text).toEqual(expect.not.stringContaining('Identity providers without access'));
            expect(text).toEqual(expect.not.stringContaining('Return to service provider'));

            await page.screenshot({path: './material/javascripts/tests/smoke/screenshots/connected-remember-choice.png'});
        });
    },
    timeout,
);
