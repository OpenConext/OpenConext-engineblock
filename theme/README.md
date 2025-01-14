## Theme Development

The layout and styling of EngineBlock are taken care of in a theme. There is a base theme, which can be overridden to suit your needs.  Below is all information you'll need to develop a theme of your own.

Take note that the development of a theme happens in the engineblock context.  Meaning you'll need a working copy of engineblock to start developing a theme.


### Tools for theme-development

For development of a theme you need to have [Node.JS][nodejs] and Yarn installed.
All other tools are installed through Node.JS with the following command:

```
(cd theme && yarn && yarn build)
```

We use the following tools:
- [SASS][sass] as a CSS extension language.  We use the .scss extension, because if you don't know sass it's just like writing regular css (it's also become industry standard).  After compilation, we use [PostCSS][postcss] to ensure vendor prefixes are added for all suported browsers.
- [Twig][twig] as the templating system.  It's well documented, easy to learn and very powerful.  If you don't know it yet you can start writing plain html and ease into it.
- [Vanilla JS][vanilla.js] as our JS solution of choice.  It's lightning fast, easily blowing any rival framework out of the water.  It also allows anyone to jump in.  We compile it using [Babel][babel] to ensure we can use the latest&greatest features of choice.
- [Cypress][cypress] for integration, end-to-end testing, visual-regression testing, accessibility-testing and html-validation.  We use plugins for the last two. The tests are situated in the `tests` folder outside the `theme` folder.

To speed up development on changes to the theme run the following command for live updates on the selected theme;
```
yarn watch
```
This will detect changes to the javascript and stylesheet in the select theme and update the build accordingly. Do note that this script only watches
the selected theme and not the base.

### Cross-browser support:

We currently support the following browsers:
- IE11 (i know)
- last two versions of Chrome
- last two versions of FF
- last two versions of Edge (chromium)
- last two versions of Safari

### Accessibility guidelines:

The entire login flow needs to conform to [WCAG][wcag] 2.1 AA-levels.

For your convenience we've added a11y-tests.scss to the base theme.  You can use it to test for the following problems:
- grayscale
- blur
- large text
- no animations
- no mouse
For instructions on how to use it: see the file itself.

### Folder structure:

- theme:
    - base: the base theme.  This is **always** the fallback theme in case a route isn't found in a custom theme.  Any theme chooses which parts to override, and which parts to simply keep.  A theme should keep the same folder structure as this theme:
        - images
        - javascripts: the main entry point for any theme is application.js
        - stylesheets: the main entry point for any theme is application.scss.  There are two secondary entry points:
            - material.scss for all templates not yet converted to the new base theme
            - error-page.scss for all error pages.
        - templates: twig templates.  The main entry points are:
            - consent.html.twig: modules > Authentication > View > Proxy
            - wayf.html.twig: same folder as above
            - redirect.html.twig: same folder again
            - index.html.twig: modules > Authentication > View > Index
            - error.html.twig: modules > Default > View > Error
        - translations: translations specific to the theme.
    - openconext: the theme formerly known as material.  This used to be the official theme.
    - scripts: node.js scripts to make our life easier.  These are called via the yarn commands.
    - skeune: the skeune theme

### Switching themes

You can switch a theme by simply changing the `theme.name` parameter in `parameters.yml`.  Use the folder name of the theme you want.  Eg. if you want the skeune theme the entry would read as follows:

```yaml
    theme.name: 'skeune'
```

if however you prefer to use the openconext theme, it'd read like this:

```yaml
    theme.name: 'openconext'
```

**Switching themes on CI**
A helper script was included in the `theme/scripts` folder to assist in building theme assets.

This script changes the Twig theme and builds the chosen frontend theme assets. To use this script you simply run (replace the skeune theme by your own theme name):

```bash
$ EB_THEME=skeune ./scripts/prepare-test.js
```

The script must be run on the engine instance on your CI environment as it also clears the application cache in order to let the correct Twig templates to be included in the cache.

### Writing your own custom theme:

#### Custom theme folder structure:

If you want to make your own theme it should be as a subfolder of the theme folder.  Name it however you want.

Your theme **must** have the following structure (or the build will fail):
- images
- javascripts: this needs to contain an `application.js` file, which is the entrypoint for all js loaded by your theme.
- stylesheets: this needs to contain an `application.scss` file, which is the entrypoint for all css loaded by your theme.
- templates: you only need to put templates here you want to override.
- translations: you only need to put files there if there are actual theme-specific translations.

For your convenience, you can simply use the following command to create a scaffold for your new theme:

`yarn create-theme <theme-name>`

For example: to create a scaffold for a new theme called "surfconext" use the command `yarn create-theme surfconext`

#### Custom CSS

Your theme needs to have `application.scss` (or .sass or .css) as an entry point for all css.  This file must be directly in the stylesheets folder (not in a subfolder).
Depending on what you do, you also may need an `error-page.scss` and `material.scss`;

This has two consequences:
1. you are completely free to use the styles you prefer.
2. if you wish to use any of the css from the base theme, you'll need to import it.

We highly recommend importing the helpers.scss file from the base theme.  It contains many useful classes, functions and mixins.
All variables in the base theme are configured to be overridable with the !default option for sass.  This means they will only be used when you have not overwritten them in your own scss files.

Last, but not least, the base theme contains an a11y-tests file.  This is meant for development only, to test a couple of common accessibility scenarios.

#### Custom Fonts

If you want to have custom fonts, put them in a fonts subfolder of stylesheets.  They will automatically be copied to the web/fonts folder when building your theme.

#### Custom Images

If you want to have custom images, put them in an images subfolder of your theme.  They will automatically be copied to the web/images folder when building your theme.

#### Custom JS

Your theme needs to have `application.js` as an entry point for all css.  This file must be directly in the javascripts folder (not in a subfolder).

This has two consequences:
1. you are completely free to use the javascript you prefer.
2. if you wish to use any of the js from the base theme, you'll need to import it.  When in doubt, you can use the application.js file in the base theme to see which files to import.

#### Custom Twig templates & some tips on testing

To override a twig file create one with the same name in the same location.  This means the folder structure of your templates needs to be the same for the templates you want to override.  Any template that isn't overriding a template from the base theme, can be in any place you want in the templates folder of your theme.

Below you'll find a list of the "entry points" for each page with corresponding testing urls to ease development.  If you want to override the entire page, you will need to have those in your theme.
- consent page:
    - `templates > modules > authentication > view > proxy > consent.html.twig`.
    - You can use `https://engine.dev.openconext.local/functional-testing/consent` to develop the page.
    - To test group memberships, you will need to make the following change to \src\OpenConext\EngineBlockFunctionalTestingBundle\Controllers\ConsentController.php:
        - find the attribute `'urn:mace:dir:attribute-def:isMemberOf'` (at the time of writing on line 93)
        - add some values to the array.  Eg:
        ```
      'urn:mace:dir:attribute-def:isMemberOf' => [
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collaboration:organisation:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                  ],
      ```

- wayf: `templates > modules > authentication > view > proxy > wayf.html.twig `.  You can use `https://engine.dev.openconext.local/functional-testing/wayf` to develop the page.
- error: `templates > modules > default > view > error > error.html.twig`.  You can use `https://engine.dev.openconext.local/feedback/unknown-error` to develop the page.
There are a lot of error pages.  To test all different kinds, you can use the urls on this page: `https://github.com/OpenConext/OpenConext-engineblock/blob/master/tests/e2e/cypress/visual-regression/ErrorPage.spec.js#L72`

- redirect page: `templates > modules > authentication > view > proxy > redirect.html.twig`.
- spinner page: `templates > modules > authentication > view > proxy > form.html.twig`.  To test it disable the onload handler on the body-tag and go to your profile (or load the page without JS).
- index.html.twig: `templates > modules > authentication > view > index > index.html.twig`.  You can use `https://engine.dev.openconext.local/` to develop the page.
- cookie removal page: `templates > modules > authentication > view > identityprovider > remove-cookies.html.twig`.  You can use `https://engine.dev.openconext.local/authentication/idp/remove-cookies` to develop the page.  The page is only accessible if you set the `wayf.remember_choice` parameter in `parameters.yml` to true.
- debug page: `templates > modules > authentication > view > proxy > debug-idp-response.html.twig`.  You can use `https://engine.dev.openconext.local/authentication/sp/debug` to develop the page.
- logout page: `templates > modules > logout > view > index > index.html.twig`.  You can use `https://engine.dev.openconext.local/logout` to develop the page.

#### Supported feature / testing flags

There are a number of feature flags which need to be supported by a theme in order to distribute it to the wider community.  These toggle certain features on or off.

**The following feature flags exist (name of variable in Twig):**
- showRequestAccess: whether or not Idps without access to the current service should be shown on the WAYF.  You can test this by going to `/functional-testing/wayf?displayUnconnectedIdpsWayf=1&unconnectedIdps=5`.
- showIdPBanner: whether to show the default IdP quick link banner on the WAYF (such as the eduId in the skeune theme).  You can test this by going to `/functional-testing/wayf?showIdPBanner=1`.
- rememberChoiceFeature: allow users to save their selected IdP and then auto-select it on returning visits.  You can test this by going to `/functional-testing/wayf?rememberChoiceFeature=1`.
- backLink: display a link which, when clicked, allows you to go back two pages.  In essence the equivalent of clicking the back button twice.  You can test this by going to `/functional-testing/wayf?backLink=1`.
- cutoffPointForShowingUnfilteredIdps: unlike the previous flags which were booleans, this one is an integer.  When this flag is present & the user is not searching (so no value in the search field): there should only be idps shown when there are no more than the cutoff point.  So as an example: if there are 100 idps that would be shown normally, and the cutoff point is 50: the user should see no idps at all (so only the empty search field is shown).  You can test this by going to `/functional-testing/wayf?cutoffPointForShowingUnfilteredIdps=50`.
- showConsentExplanation: whether to show a special explanation on the consent page.  There is currently no test endpoint for this.
- showGlobalSiteNotice: whether to show a global site notice or not.  You can test this by going to `/functional-testing/wayf?showGlobalSiteNotice=1` or `/functional-testing/consent?showGlobalSiteNotice=1`
- globalSiteNotice: if you want to add a siteNotice you can add the text for it here.  You can test this by going to `/functional-testing/wayf?showGlobalSiteNotice=1&globalSiteNotice=<text>` or `/functional-testing/consent?showGlobalSiteNotice=1&globalSiteNotice=<text>`

There is currently one flag which allows for testing a more realistic scenario.  The number of such flags might be expanded in the future.

**The following test flag exist (name of query param):**
- randomIdps: whether to use random Idp names (taken from a selection of real IDP names) with random connected status.  Example usage:
`https://engine.dev.openconext.local/functional-testing/wayf?randomIdps=20&displayUnconnectedIdpsWayf=true`
There are only 25 random names to choose from.  If you enter a number larger than 25, number 26 & greater will receive names as normal for the FT idps.
**Note:** these idps are randomly assigned connected / unconnected status.

[babel]: https://babeljs.io/
[cypress]: https://www.cypress.io/
[nodejs]: https://nodejs.org/en/
[postcss]: https://postcss.org/
[sass]: https://sass-lang.com/
[twig]: https://twig.symfony.com/
[vanilla.js]: https://learnvanillajs.com/
[wcag]: https://www.w3.org/WAI/standards-guidelines/wcag/
