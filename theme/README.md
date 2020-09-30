## Theme Development

The layout and styling of EngineBlock are taken care of in a theme. There is a base theme, which can be overriden to suit your needs.  Below is all information you'll need to develop a theme of your own.


### Tools for theme-development

For development of a theme you need to have [Node.JS][nodejs] installed.
All other tools are installed through Node.JS with the following command:

```
(cd theme && npm install && npm run build)
```

We use the following tools:
- [SASS][sass] as a CSS extension language.  We use the .scss extension, because if you don't know sass it's just like writing regular css (it's also become industry standard).  After compilation, we use [PostCSS][postcss] to ensure vendor prefixes are added for all suported browsers.
- [Twig][twig] as the templating system.  It's well documented, easy to learn and very powerfull.  If you don't know it yet you can start writing plain html and ease into it.
- [Vanilla JS][vanilla.js] as our JS solution of choice.  It's lightning fast, easily blowing any rival framework out of the water.  We compile it using [Babel][babel] to ensure we can use the latest&greatest features of choice.
- [Cypress][cypress] for integration, end-to-end testing, visual-regression testing & even for accessibility-testing and html-validation (through plugins).

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
    - base: the base theme.  This is **always** the fallback theme in case a route is not found in a custom theme.  Any theme chooses which parts to override, and which parts to simply keep.  A theme should keep the same folder structure as this theme:
        - images
        - javascripts: the main entry point for any theme is application.js
        - stylesheets: the main entry point for any theme is application.scss
        - templates: twig templates.  todo give an overview of the main entry points
    - cypress: todo figure out if it'll stay in this structure or not
    - openconext: the theme formerly known as material.  This used to be the official theme, but is now second-fiddle (as it does not conform to the accessibility standards)
    - scripts: currently holds the build-script, written in node.js.
    - skeune: todo delete this

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

This script changes the Twig theme and builds the chosen frontend theme assets. To use this script you simply run:

```bash
$ EB_THEME=skeune ./scripts/prepare-test.js
```

The script must be run on the php-fpm instance on your CI environment as it also clears the application cache in order to let the correct Twig templates to be included in the cache.

### Writing your own custom theme:

#### Custom theme folder structure:

If you want to make your own theme it should be as a subfolder of the theme folder.  Name it however you want.

Your theme **must** have the following structure:
- images
- javascripts: this needs to contain an `application.js` file, which is the entrypoint for all js loaded by your theme.
- stylesheets: this needs to contain an `application.scss` file, which is the entrypoint for all css loaded by your theme.
- templates: you only need to put templates here you want to override.

For your convenience, you can simply use the following command to create a scaffold for your new theme:

`npm run create-theme <theme-name>`

For example: to create a scaffold for a new theme called "surfconext" use the command `npm run create-theme surfconext`

#### Custom CSS

Your theme needs to have `application.scss` (or .sass or .css) as an entry point for all css.  This file must be directly in the stylesheets folder (not in a subfolder).

This has two consequences:
1. you are completely free to use the styles you prefer.
2. if you wish to use any of the css from the base theme, you'll need to import it.

We highly recommend importing the helpers.scss file from the base theme.  It contains many usefull classes, functions and mixins.
All variables in the base theme have been configured to be overridable with the !default option for sass.  This means they will only be used when you have not overwritten them in your own scss files.

Last, but not least, the base theme contains an a11y-tests file.  This is meant for development only, to test a couple of common accessibility scenarios.

#### Custom Fonts

If you want to have custom fonts, put them in a fonts subfolder of stylesheets.  They will automatically be copied to the web/fonts folder when building your theme.

#### Custom Images

If you want to have custom images, put them in a images subfolder of your theme.  They will automatically be copied to the web/images folder when building your theme.

#### Custom JS

Your theme needs to have `application.js` as an entry point for all css.  This file must be directly in the javascripts folder (not in a subfolder).

This has two consequences:
1. you are completely free to use the javascript you prefer.
2. if you wish to use any of the js from the base theme, you'll need to import it.  When in doubt, you can use the application.js file in the base theme to see which files to import.

#### Custom Twig templates

To override a twig file create one with the same name in the same location.  This means the folderstructure of your templates needs to be the same for the templates you want to override.  Any template that isn't overriding a template from the base theme, can be in any place you want in the templates folder of your theme.

Below you'll find a list of the "entry points" for each page with corresponding testing urls to ease development.  If you want to override the entire page, you will need to have those in your theme.
- consent page: `templates > modules > authentication > view > proxy > consent.html.twig`.  You can use `https://engine.vm.openconext.org/functional-testing/consent` to develop the page.
- wayf: `templates > modules > authentication > view > proxy > wayf.html.twig `.  You can use `https://engine.vm.openconext.org/functional-testing/wayf` to develop the page.
- error: `templates > modules > default > view > error > error.html.twig`.  You can use `https://engine.vm.openconext.org/feedback/unknown-error` to develop the page.

[babel]: https://babeljs.io/
[cypress]: https://www.cypress.io/
[nodejs]: https://nodejs.org/en/
[postcss]: https://postcss.org/
[sass]: https://sass-lang.com/
[twig]: https://twig.symfony.com/
[vanilla.js]: https://learnvanillajs.com/
[wcag]: https://www.w3.org/WAI/standards-guidelines/wcag/
