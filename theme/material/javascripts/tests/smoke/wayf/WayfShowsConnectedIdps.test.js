const timeout = 5000;

describe(
    'WAYF is showing connected IdPs by default',
    () => {
        let page;
        beforeAll(async () => {
            page = await global.__BROWSER__.newPage();
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf');
        }, timeout);

        it('Should show five connected IdPs and the search field', async () => {
            const text = await page.evaluate(() => document.body.textContent);
            const searchFieldValue = await page.evaluate(() => document.querySelector('.mod-search-input').attributes.getNamedItem('placeholder').value);
            let idps = await page.evaluate(() => [...document.querySelectorAll('#connected-idp-picker h3')].map(elem => elem.innerText));
            expect(text).toContain('Select an organisation to login to the service');
            expect(idps).toHaveLength(5);
            // Verify the IdP is set with a title
            expect(idps[2]).toEqual('Connected IdP 3');
            expect(searchFieldValue).toEqual('Search for an organisation...');

            // Test the search option by filtering on IdP 4, should yield one search result
            await page.type('.mod-search-input', 'IdP 4');

            idps = await page.evaluate(() => [...document.querySelectorAll('#connected-idp-picker h3')].map(elem => elem.innerText));
            expect(idps).toHaveLength(1);
            expect(idps[0]).toEqual('Connected IdP 4');

            // Ensure some elements are not on the page
            expect(text).toEqual(expect.not.stringContaining('Identity providers without access'));
            expect(text).toEqual(expect.not.stringContaining('Remember my choice'));
            expect(text).toEqual(expect.not.stringContaining('Return to service provider'));

        });
    },
    timeout,
);