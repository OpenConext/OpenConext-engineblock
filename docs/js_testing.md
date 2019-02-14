# JavaScript testing

The EngineBlock components that interface with the user in a graphical user interface rely on Javascript to function 
correctly. In order to prevent breakage of these components, Javascript (Jest) functional / acceptance tests have been 
written to try and prevent regressions from happening.

## Running tests
Jest is used to run unit tests. Puppeteer is used on top of Jest in order to run end to end (smoke) tests. These tests 
can be run in several ways:

**Unit tests**

`ant js-unit-tests` should work after installing the NPM dependencies in the `theme` folder. This will only run the unit 
tests from the `theme/material/javascripts/tests/unit` directory. If you are building a custom theme, be sure to update
the `build.xml` file, and change the target to your theme.

**Smoke tests**
 
`ant js-smoke-tests`

Running  `npm run jest` can also be used from the `theme` folder, this will run all jest tests that can be found in the project.

# WAYF tests
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
| connectedIdps | (int) 5 | Type: integer. The number of IdPs to display on the WAYF. | 
| unconnectedIdps | (int) 0 | Type: integer. The number of unconnected IdPs to display on the WAYF. | 
| backLink | (bool) false | Type: boolean. Display the back link on the WAYF. | 
| lang | (string) 'en' | Type: string. The language to use when rendering the WAYF. Note that it's possible that this is overridden by the cookie value. | 

## Acceptance tests
The WAYF acceptance tests utilize the `/functional-testing/wayf` endpoint in order to test the correct inner working of
the WAYF.
