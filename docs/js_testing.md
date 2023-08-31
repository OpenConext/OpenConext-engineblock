# JavaScript testing

The EngineBlock components that interface with the user in a graphical user interface rely on Javascript to function
correctly. In order to prevent breakage of these components, Javascript (Jest) functional / acceptance tests have been
written to try and prevent regressions from happening.

## Running tests
Cypress is used to run Javascript tests.

**Unit tests**

`ant js-unit-tests` should work after installing the NPM dependencies in the `theme` folder. This will only run the unit
tests from the `theme/material/javascripts/tests/unit` directory. If you are building a custom theme, be sure to update
the `build.xml` file, and change the target to your theme.

**Smoke tests**

`ant js-smoke-tests`

Running  `npm run jest` can also be used from the `theme` folder, this will run all jest tests that can be found in the project.

Note that the `expect-puppeteer` package is used to perform more efficient interactions and assertions on the DOM. Expect-puppeteer states:

> Writing integration test is very hard, especially when you are testing Single Page Applications. Data are loaded asynchronously and it is difficult to know exactly when an element will be displayed in the page.
  Puppeteer API is great, but it is low level and not designed for integration testing.

More info about this package can be found at their [GitHub](https://github.com/smooth-code/jest-puppeteer/tree/master/packages/expect-puppeteer)

## Test endpoints

### WAYF
The WAYF strongly relies on JavasScript. It is used to build the various IdP lists (connected, unconnected and previously selected).
But is also used to search through the available IdP's, keypress navigate and display the request access lists.

In order to make life of the developers easier, a WAYF mock endpoint was created. Making full use of the real WAYF
Twig template, but with the capability to quickly configure it with the different options that would otherwise need to
be set with Manage.

The test endpoint is available in `test` and `dev` environments on the `/functional-testing/wayf` endpoint. The following
parameters can be used to manipulate the behaviour of the wayf that is rendered.

| **Query parameter** | **Default value** | **Explanation** |
|---|----|----|
| displayUnconnectedIdpsWayf | (bool) false | Type: boolean. Display unconnected IdPs on the WAYF. |
| rememberChoiceFeature | (bool) false | Type: boolean. Display the remember choice feature. |
| cutoffPointForShowingUnfilteredIdps | (int) 100 | Type: integer. The number of IdPs to display on the WAYF before cutting them off. |
| showIdpBanner | (bool) true | Type: boolean. Show the EduId (default IdP) banner on the WAYF or not |
| defaultIdpEntityId | (string) null | Type: string. The entityId of the default IdP (EduId) |
| connectedIdps | (int) 5 | Type: integer. The number of IdPs to display on the WAYF. |
| unconnectedIdps | (int) 0 | Type: integer. The number of unconnected IdPs to display on the WAYF. |
| backLink | (bool) false | Type: boolean. Display the back link on the WAYF. |
| lang | (string) 'en' | Type: string. The language to use when rendering the WAYF. Note that it's possible that this is overridden by the cookie value. |

### Feedback (error) pages
Many different custom error pages can be raised in many different conditions. It is quite tedious to raise those errors from the code as it sometimes is hard to simulate the error situations. This called for a test endpoint that allows us to easily recreate error screens.

The test endpoint is available in `test` and `dev` environments on the `/functional-testing/feedback` endpoint. The following
parameters can be used to manipulate the behaviour of the wayf that is rendered.

| **Query parameter** | **Default value** | **Explanation** |
|---|----|----|
|template|Format: `string`<br>Value:`session-lost`|Render the error feedback template, identified by one of the templates found in `theme/material/templates/modules/Authentication/View/Feedback`|
|feedbackInfo|Format: `JSON`<br>Value:`{"requestId":"5cb4bd3879b49","ipAddress":"192.168.66.98","artCode":"31914"}`|Can be filled with any parameter that can be rendered in the feedback information section of the feedback page.|
|parameters|Format: `JSON`<br>Value:`{}`|Some templates might require additional twig parameters, this parameter allows you to pass these additional parameters in JSON format.|

For a list of realistic reproductions of the available error pages, see this JavasScript test: `tests/e2e/cypress/integration/visual-regression/error-page/ErrorPage.spec.js`

### Consent
The consent screen is available on: `/functional-testing/consent`


| **Query parameter** | **Default value** | **Explanation** |
|---|----|----|
| name-id | (string) 'user@openconext' | Type: string. The name id of the authenticating user |
| idp-name | (string) 'DisplayName' | Type: string. The name displayed for the IdP that released the attributes |
| sp-name | (string) 'DisplayName' | Type: string. The name displayed for the authenticating service provider |
| hide-header | (bool) true | Type: boolean |
| hide-footer | (bool) true | Type: boolean |
| persistent-name-id | (bool) true | Type: boolean. Is the name id configured to be persistent? This affects the rendering of the attributes |
| aa-enabled | (bool) true | Type: boolean. Is attribute aggregation enabled, if flagged, two dummy AA sources are configured. And two attributes from those sources are included in the attribute list (EckID & OrcID ID).  |

## Acceptance tests
The WAYF acceptance tests utilize the `/functional-testing/wayf` endpoint in order to test the correct inner working of
the WAYF.

## Visual regression tests
While building the new error page styling, visual regression testing was utilized to take control over visual regressions. The `cypress-plugin-snapshots` <sup>1</sup> package is used to perform these tests.

Running them is as simple as:
`EB_THEME=theme_name ant js-visual-regression-tests`
or if you prefer the cypress CLI
`$ EB_THEME=theme_name npm run test:visual-regression`
Do note that you need to set the EB_THEME env variable before running the tests!

Snapshots are stored in `__image_snapshots__` directories in a subfolder of the `tests/e2e/cypress/integration/visual-regression/theme_name` directory, here you will also find diffs if ever your snapshot diverges from the previous snapshot.

:warning: These tests are considered risky tests and are not run on every QA build.

[1] https://github.com/meinaart/cypress-plugin-snapshots
