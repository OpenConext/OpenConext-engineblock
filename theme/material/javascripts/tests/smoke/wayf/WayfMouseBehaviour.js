const timeout = 40000;

describe(
    'WAYF can be used with a mouse',
    () => {
        let page;
        beforeAll(async () => {
            page = await global.__BROWSER__.newPage();
            const override = Object.assign(page.viewport(), {width: 1920, height: 1080});
            page.setViewport(override);
        }, timeout);

        /**
         * Reproduction of the behaviour, described in:
         * https://www.pivotaltracker.com/story/show/165056451
         */
        it('Disconnected IdPs should be highlighted on mouse hover', async () => {
            // Open a dummy wayf with 5 connected IdPs and 5 unconnected IdPs
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=10&displayUnconnectedIdpsWayf=true&unconnectedIdps=5');
            // Wait for the unconnected idp picker to load.
            await page.waitFor("#unconnected-idp-picker");
            //await page.mouse.move(900, 720);
            await page.hover('#unconnected-idp-picker > div > div.idp-list > a.result.active.noaccess:nth-child(1)');
            const disconnectedIdP1 = await page.$eval('#unconnected-idp-picker > div > div.idp-list > a.result.active.noaccess:nth-child(1)', e => e.className);
            // The previously chosen IdP list must now be on the page
            expect(disconnectedIdP1).toContain("focussed");
        });

        /**
         * Reproduction of the behaviour, described in:
         * https://www.pivotaltracker.com/story/show/165021022
         */
        it('Connected IdP should respond to mouse click after clearing previous selections', async () => {
            // Open a dummy wayf with 5 connected IdPs
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5');
            // Click the first IdP, adding it to the list of previously chosen IdPs
            await page.click('a.result.active.access:nth-child(1)', {'button': 'left'});
            await page.waitForNavigation();
            expect(await page.url()).toBe('https://engine.vm.openconext.org/');
            // Go back to the WAYF
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5');
            // Click another IdP, adding a second entry to the preselection
            await page.click('a.result.active.access:nth-child(2)', {'button': 'left'});
            await page.waitForNavigation();
            // Go back to the WAYF
            await page.goto('https://engine.vm.openconext.org/functional-testing/wayf?connectedIdps=5');
            const previouslyChosenTitle = await page.$eval('div.preselection header h2', e => e.innerHTML);
            // The previously chosen IdP list must now be on the page
            expect(previouslyChosenTitle).toContain("Previously chosen:");
            // Next click the edit button
            let editButton = await page.$eval('.edit', e => e.innerHTML);
            expect(editButton).toBe("edit");
            await page.click('.edit', {'button': 'left'});
            // And remove the one entry from the list of previously chosen IdPs
            await page.click('span.deleteable', {'button': 'left'});
            // Again, click the edit button (which now shows the done text)
            editButton = await page.$eval('.edit', e => e.innerHTML);
            expect(editButton).toBe("done");
            await page.click('.edit', {'button': 'left'});
            // Finally click one of the Connected IdPs
            await page.click('a.result.active.access:nth-child(1)', {'button': 'left'});
            await page.waitForNavigation();
            expect(await page.url()).toBe('https://engine.vm.openconext.org/');
        });
    },
    timeout,
);
