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

You can switch a theme by simply changing the `theme.name` parameter in `parameters.yml`.  Use the folder name of the theme you want.  Eg. if you want the base theme the entry would read as follows:

```yaml
    theme.name: 'base'
```

if however you prefer to use the openconext theme, it'd read like this:

```yaml
    theme.name: 'openconext'
```

### Writing your own custom theme:

#### Custom theme folder structure:

If you want to make your own theme it should be as a subfolder of the theme folder.  Name it however you want.

Your theme **must** have the following structure:
- images
- javascripts: this needs to contain an `application.js` file, which is the entrypoint for all js loaded by your theme.
- stylesheets: this needs to contain an `application.scss` file, which is the entrypoint for all css loaded by your theme.  If you want to have custom fonts, put them in a `fonts` subfolder here (and import them in your css).
- templates: you only need to put templates here you want to override.

For your convenience, you can simply use the following command to create a scaffold for your new theme:

`npm run create-theme <theme-name>`

For example: to create a scaffold for a new theme called "surfconnext" use the command `npm run create-theme surfconnext`

#### Custom CSS

#### Custom JS

#### Custom Twig templates



[nodejs]: https://nodejs.org/en/
[sass]: https://sass-lang.com/
[postcss]: https://postcss.org/
[twig]: https://twig.symfony.com/
[cypress]: https://www.cypress.io/
[vanilla.js]: https://learnvanillajs.com/
[babel]: https://babeljs.io/
[wcag]: https://www.w3.org/WAI/standards-guidelines/wcag/
