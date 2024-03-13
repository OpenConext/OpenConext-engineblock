# CHANGELOG
All release notes between version 4.7.5 and 5.8.0 can be found on [GitHub](https://github.com/OpenConext/OpenConext-engineblock/releases). Release notes for version <= 4.7.5 are in the repository under `docs/release_notes`.

We will continue to post relevant release notes on the GitHub release page. More detailed release notes should be placed in this document.

More information about our release strategy can be found in the [Development Guidelines](https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines#release-notes) on the EngineBlock wiki.

## 6.14.0
* Support overriding StepUp EntityId #1279
* Check for needed parameters before constructing an auth failed response. #1289

## 6.13.0

* Move most HTML from translatable strings into Twig templates, where it
  belongs. This makes the code more robust and predictable, and reduces
  the chances of cross site scripting injections. Some translatable strings
  were changed, see upgrade notes.
* Install a NPM package update.

## 6.12.2

* Add optional configurable environment-specific ribbon to top-right of UI.
* Fix some Request Access bugs.
* Fix Cypress tests.
* Install NPM package updates and switch to yarn as package manager.

## 6.12.1
* Repaired tag-release blockage

## 6.12.0
**Feature**
* Inject the logger on StepupDecision #1254

**Bugfix**
* Improve MDUI Logo usage #1264

**Improvement**
* Documentation regarding the `metadata:coin:stepup:forceauthn` feature was added. 
* Specify data types of roles columns #1262

**Maintenance**
* Test integration Github Action utilizes base container
* Test integration runs against PHP 7.2 and PHP 8.2 (removed PHP 5.6 test runs) 

## 6.11.0
**Feature**
* Allow to set EB's metadata XML Organization fields via translation (overrides).
* Refuse to process incoming metadata push with 0 connections in it.
* Show an Unknown SP error page also when invoking the unsolicited endpoint with an unknown entity ID.
* Update regular expression for URN validation.

**Change**
* Migrate storage of MDUI metadata elements to new value objects.

**Bugfix**
* Avoid generating PHP notices when calling metadata API for IdP without SLO endpoints.
* Update log message for session lost to not mention irrelevant 'unsolicited'.

## 6.10.0
**Feature**
* Support setting forceAuthn flag on Stepup callout when enabled in Manage for the service.

## 6.9.2
**Change**
* Log IDPList contents (info) only when present. Also log any IDPList contents to log_logins.
* Do not list redundant key:default metadata URL variants on front page.
* Give an explict error message when an SP requests an unknown key ID.
* Support MySQL strict mode by limiting some metadata fields on push to 255 characters.

**Bugfix**
* Fix certificate used for Stepup callout changing based on SP selected key ID.

**Maintenance**
* Move certificate serving code to Symfony.
* Clean up or disable some unused bits.
* Update NPM json5 dependency.

## 6.9.1
**Bugfix**
* Change migration for consent table to work when running 6.9 in parallel with an earlier version (rolling upgrade).
* Change loglevel for unknown entityID in RequesterID from warning to info.

**Maintenance**
* Remove broken and unused WAYF sorting code.
* Add more integration tests for WAYF scoping.

## 6.9.0
**Change**
* Add support for OpenConext Stepup LoA 1.5. This requires renumbering the `stepup.loa.mapping` indices in parameters.yml from (1,2,3) to (10,15,20,30).

**Maintenance**
* Fix call for rememberChoice in the WAYF.
* Update Twig dependency.
* Clean up some legacy code.

## 6.8.1
**Feature**
* Several improvements to IdP debug page
* Support subject-id attribute, also for scope checks
* When consent feature flag is off, also disable the consent API

**Maintenance**
* Update various JavaScript dependencies

## 6.8.0
**Feature**
* Support removal (soft-delete) of consent by collabPersonID and SP entity id #1160

**Maintenance**
* Request Access JavaScript bugs have been addressed #1187 #1188

## 6.7.2
**Feature**
1. The consent API (used by Profile) now additionally returns the SP's organization name in the `organization_display_name` key.

**Maintenance**
1. Upgrade some npm packages.

## 6.7.1
**Feature**
1. Introduce SSO Session Cookie support (feature flag defauls to _off_).
   More information in [the feature documentation](./docs/sso_session_cookie.md).

**Bugfixes**
1. Fix the initial (pre-search) sorting of entities in the WAYF.
1. In Consent, display NameID of the end-SP, not trusted proxy.
1. Display correct information in error message for unknown proxied SP.

**Maintenance**
1. Improve test coverage for Trusted Proxy scenarios.
1. Upgrade some npm packages.

## 6.7.0
**Feature**

1. In 6.7.0 a new behaviour is introduced when dealing with trusted proxies. More information about these changes can
   be found in the updated [trusted proxy documentation](./docs/trusted_proxy.md). You should be able to inform yourself
   what changes are required, when you are working with a trusted proxy.

   The following work was done to get to this feature:
    - Cover internal-CollabPersonId behaviour in test #1172
    - Apply trusted proxy related output filtering #1175
    - Trusted proxy documentation update #1174
    - Update attribute dictionary #1177

2. A long awaited tag release GitHub Actions workflow was added to this release.
  - Test release creation in CI/CD pipeline #888 (thanks for the groundwork @pablothedude!)

**Maintenance**
 - Upgrade Twig to 2.x

## 6.6.6
**Features**
 - Check if there is a valid authentication in the AuthenticationState
 - Add "consent disable" feature
 - Add behat test for consent enabled feature toggle
 - Fixed the build and added a watch script

## 6.6.5
**Feature**
 - Upgrade the Monitor bundle, exposing opcache statistics to the info endpoint #1163

## 6.6.4
**Features**
- allow control over the RequestedAttribute list added to the proxy SP metadata

**Bugfix**
- reset attributeValueTypes after an attribute manipulation

**Security**
- upgrade a dependency

## 6.6.3
**Features**
- add ARP feature
- the AuthnRequest is now available to Attribute Manipulations.
- add ability to process SSO Notifications
- unset the autogenerated id field so databases other than Mysql & MariaDB are supported as well.

**Bugfixes**
- a typo was fixed


## 6.6.2
**Changes**
- ARP is only applied once in the filter chain. This should only affect Attribute Manipulations (in being more permissive in what is possible).

**Bugfixes**
- fix the spinner page in the excel built-in browser
- minor change to privacy policy link on consent screen

**Security**
- upgrade several dependencies to fix security vulnerabilities #1146, #1149, #1151


## 6.6.1
**Bugfixes**
- style the no-attributes edge case #1144
- temporarily disable back to sp link to allow time for a full fix #1143
- ensure textual fallbacks in the error pages function correctly #1135

**Security**
- upgrade several dependencies to fix security vulnerabilities #1142, #1134, #1133, #1132

## 6.6.0
**Features and bugfixes**
- Add a global site notice #1128
- Allow AuthnRequest based stepup requests #1105
- Add a 'back to SP' button on the 'Stepup failed' error pages #1114
- Error messages have been improved #1112 #1130
- Include Twig linting in CI pipeline #1108
- Transparent SAML error Response for MFA entities #1131
- Remove double titles on skeune error pages #1110
- Update visual screenshots & tests for them #1109
- Ensure search results remain after ENTER #1106
- Replace the hamburger and search icon #1116
- Change the default mail configuration to SMTP instead of sendmail #1129

## 6.5.1
**Bugfix**
- Remove forgotten debug statement #1107

## 6.5.0
A bit of everything release. Most notable changes are:

**Features and bugfixes**
- Support for Stepup LoA based on PDP decision #1088
- The logout page was styled #1097
- The IdP SSO endpoint URI used for EngineBlock authentications is now logged #1095
- Translation log-spam issues where resolved #1096
- Translation override issues where addressed #1098
- Accessability improvements have been applied to the WAYF #1099

## 6.4.7
**Features and fixes**
- Replaced the SURFnet favicon by the openconext one
- Keep the WAYF operable when syncing cookies.
- Add logging to ValidateMfaAuthnContextClassRef command.
- Tweak NL language.
- Correct display of tooltip for group attributes.
- Avoid double encoding logo url for attribute source.
- Ensure images after search are ok in IE11.
- Prevent logo flicker in FF whenever possible.
- Ensure Idps after sync are deletable.
- Prevent Idp-row logo from overlapping with text.

**Security**
- Replaced the composer dependency for the security checker (no longer working) with a local one.

## 6.4.6
**Features and fixes**
 - Fixed disabled button being shown in the remaining section for non-disabled accounts on IE11
 - Added a check to see if there is a focused element before the check to see if the focus is on an arrow item.
 - Cleaned up forgotton debug code.

## 6.4.5
**Features and fixes**
 - Repaired search in IE
 - Fixed a visual regression with the previously selected section where the edit button was on it's own line.

## 6.4.4
**Features and fixes**
 - Fixed overflow issue in IE11 for disclaimerlist.
 - Updated the text for the PEP page.

## 6.4.3
**Security update**
 - Bumped socket.io version.

**Features and fixes**
 - Ensured hitting the reset button on the WAYF shows the default IDP
 - Ensured that if you click the default idp, it prefills it in the search field, hides all other idps and focuses it.
 - Remove weird transition in safari.
 - Ensured search works in IE11.
 - Amended faulty arrow behaviour.
 - Ensured selected idp cookie behaviour works as expected.
 - Redesigned the spinner page (form page).
 - Added a hover style for disabled accounts.
 - Added arrow behaviour on hover as in the old WAYF.
 - Coupled cypress tests to the same selectors as the JS.
 - Adjusted styling & wording of the PEP page.

## 6.4.2
**Security update**
 - Allowed connections where not checked against the correct SP when trusted proxy was used

**Other**
 - Updated translations for new theme

## 6.4.1
**Features and fixes**
 - The index, debug and cookie pages have been styled to prevent the mixed new and old style that happened. They now all look like the previous OpenConext theme used to look.
 - Security and compatibility improvements have been applied.
 - The new theme is now the default theme

## 6.4.0 (RC 1)
This release consists of the UI redesign of the WAYF, Consent and other user facing screens.
The complete list of changes is excessive. Details can be found on GitHub under the `ui-redesign` tag. Some highlights
include:

**Features**
- Complete redesign of the WAYF
    - The WAYF now includes an optional default IdP banner, making advertising a default IdP possible (eduId)
    - The WAYF is optimized for keyboard navigation
- Redesigned the consent screen
    - The previously optional minimal consent screen is now the default
    - Tooltips are now pure HTML/CSS, no eternal JS libraries are used
    - Keyboard navigation was improved greatly
- Both the WAYF and the consent screen are optimized for the optically or visually impaired. The interface is not yet
  auditted, but a WCAG 2.1 AA is to be expected.

## 6.3.6
After some testing, @tvdijen opened issue #920, identifying several issues with the 3.6.x releases. Those issues
have been addressed in this release.

**Bugfixes**
* Allow responses without NameID #919
* Add c14n method to the reference transforms in XML metadata. #921
* Prevent undefined access in Assembler #923

**Chore**
* Remove the remaining eduGAIN metada fields #922

## 6.3.5
**Bugfix**
 - Clean up unused usage of AuthnRequest destination #898

## 6.3.4
**Bugfix**
 - Whether MFA AuthnContext must be added should be based on original SP #893 #894

## 6.3.3
**Bugfixes:**
 - Move NoPassive response processing up in the ACS proces #890
 - Print the key-id in the SSO locations of the IdP metadata #891

**Security**
 - Upgrade jpeg-js to v0.4.0 #892

## 6.3.2
**Bugfix:**
 - Store entityId of issuer, not the value object #889

**Features:**
 - Migrate existing JavaScript tests to Cypress #887

## 6.3.1
As of this release the old non conforming Schac Home Organization synonym: urn:oid:1.3.6.1.4.1.1466.115.121.1.15 is no longer released as an attribute. This was achieved by removing it from the attributes.json. If you need it, please place it back in ./application/configs/attributes.json. See UPGRADING.md for details.

This release also includes the introduction of the Cypress test framework for JavaScript testing. The test framework does not yet run correctly on the GitHub Actions CI integration. This is corrected in the next release.

**Features:**
 - Remove non conforming SHO oid from config #877
 - Send NoPassive status response back to issuing SP #885

**Improvements:**
 - Upgrade SAML2 library to version v4.1.9 #881
 - Show proxied SP and proxy in feedback info #875
 - Move metadata organization business rules away from metadata assembler #878
 - Add trusted proxy signing verification #879
 - Migrated a JavaScript test to Cypress (POC) #884

**Chores:**
 - Repair acceptance tests #880
 - Upgrade dot-prop to version 5.2.0 #886
 - Change symfony cache path to reflect deploy path #857

## 6.3.0
## 6.2.4
This release is the finalization of the AuthnContextClassRef changes
that where started in 6.2.1 (and rolled back in 6.2.2).

**Features**
 * Add AuthnContextClassRef config option for transparent RequestedAuthnContext #873

**Other chores**
 * Final tweaks to Github Actions (termination of Travis) #867
 * Enable skipped API tests #874

## 6.2.3
This change will add the possibility to configure authn contexts for IdP/SP combinations which will be verified when returning from the IdP

see: [documentation](docs/configurable_authncontextclassref.md)

**Features**
 * Add custom MFA error page #866
 * Add MFA authncontext response validator #864
 * Test unsolicited presence of authcontextclassref #863
 * Add authncontextclassref to SP if configured in IdP #861
 * Add authncontextclassref documentation #862
 * Assemble authcontextclassref combinations #859
 * Add dockerized actions testing #818

**Improvements**
 * Pt translation fix #870 thanks @domgon!


## 6.2.2
This change will revert #848 to prevent breaking flows because of misconfigured SP's

 * If the SP provided a RequestedAuthnContext in the AuthnRequest, replicate this to the IdP #848

## 6.2.1
**Improvements**
 * Make support urls translatable #851
 * Allow empty sfo/stepup key and use JIT validation instead #853
 * If the SP provided a RequestedAuthnContext in the AuthnRequest, replicate this to the IdP #848

**Bugfix**
 * Log AuthnContextClassRef and correct NameId on a successful login #854

## 6.2.0
This release replaces the legacy configuration with Symfony configuration. So effectively the `application.ini` is removed from EB and replaced in favour of a `parameters.yml`.
Also, EB now has Portuguese language support, and the allowed languages are configurable.

**Features**
 * Remove legacy application.ini from EB #838
 * Make the enabled languages configurable #842
 * Add Portuguese language support #841
 * Log original NameID and given LOA on successful login #845

**Improvements**
 * Move footer translations to translations #844
 * Remove unused error pages #849
 * Cleanup unused `response_processing_service_binding` column from the database #782
 * Implement lazy loading of WAYF logos. #843

**Chores**
 * Stop generating bootstrap.php.cache #837
 * Remove old (IE8 / IE9) browser support #846
 * Defense in depth SAML Response validation #806
 * Disassemble the EB debug feature #836
 * Remove remaining eduGAIN code from EngineBlock #834
 * Prevent recurring migration creation #833
 * Change language cookie defaults #832

## 6.1.3
Changes done in order to confine to metadata spec to pass validation.

 * According to the spec, EmailAddress needs to have a mailto: prefix #827
 * Remove version number from attributes.json config file. #828
 * Add explicit reference to metadata xml signature. #830


## 6.1.2
Adds a PR that was missing in 6.1.1 which will ensure a suitable displayname is displayed in the metadata EB produces.

  * Metadata UI Info optimalisation #824

## 6.1.1
Changes to make the metadata more aligned with the SAML metadata specification.

 * Add keywords to each idp entry in idps-metadata #825
 * Empty displayname/description in idps-metadata #825
 * md:Organization block missing in idp metadata and misses url #826

## 6.1.0
The metadata endpoints of EngineBlock have been under heavy maintenance in this release. Some highlights include the
move of all generation logic to the new Symfony EngineBlock application. But maybe more important, EngineBlock no longer
relies on entity information from the `sso_provider_roles_eb5` table. All EngineBlock metadata is either loaded from
ini config, or is hardcoded into the application. Because the Eb entities were purely an internal EB necessity you could
now remove them by removing them from Manage and then execute a metadata push.

The unused metadata entities are the following:
* engine.{{ base_domain}./authentication/sp/metadata
* engine.{{ base_domain}//authentication/idp/metadata

**Features**
 * Twig is used as template engine #759
 * User friendly errors are displayed when metadata can not be created #770 (resolves issue #211)
 * Metadata is now generated in the Symfony EngineBlock application #765 #771 #772 #773 #776 #783 #784 #785 #791
 * The EngineBlock home screen was updated (slightly) #768 #769
 * eduGAIN support was removed from the project #767
 * Remove unused metadatagetOrganizationUrlNl entities and logic #811

**Improvements**
 * Test coverage was a high priority (unit and functional tests are provided for every important feature) #766 #779 #780 #794 #795

**Other chores**
 * Third party dependencies: Doctrine ORM, PHPUnit, Phake and Mockery have been updated  #764 #777
 * Update ChromeDriver to match Chrome version #793
 * The dev and test cache locations have been moved to a location outside the project directory #780
 * Technical documentation was added to the wiki.

## 6.0.2
This is a hotfix to fix namespaces after a commit was cherry picked.

**Bugfix**
 * Fix namespace after cherry picked bugfix #820


## 6.0.1
This release is a bugfix release to prevent the 5.13 release from becoming broken after migrations running the migrations in the 5.13 release.
The migratoins dropped columns which still were in  use by 5.13 and is needed to support the rolling updates.

**Bugfix**
 * Remove migration to support 5.13 #817
 * Prevent invalid assertion when stepup LoA is set #819


## 6.0.0
In this release, PHP 5.6 support was dropped in favour of PHP 7.2. We are not migrating to the latest PHP version for some reasons. Chief amongst which is that PHP 7.2 was best compatible with the current EngineBlock code base.
An upgrade to 7.3 or even 7.4 would force us to upgrade many third party dependencies at the same time, making this release much bigger.

For installation instructions, see the UPGRADING.md entry for this version.

The following changes where introduced in this release:

**Improvements**
* PHP 7.2 compatibility changes #713
* Prevent WAYF button from floating left #760
* Verify received LoA in StepUp ACS processing step #800
* Added IDPentityID to the Attribute Aggregation request #799 (thanks @ohastra)

**Bugfix**
* Get termsOfServiceUrl from coins, not SP metadata entity #756

**Other chores**
* Cleanup old coin columns #755
* Update Composer settings for improved PHP 7.2 support #763
* Update testing tools (PHPUnit, Mockery & Phake) #777
* Fixed typos #790 #809

**Security**
* Upgrade xmlseclibs to version 3.0.4 #802
* Upgrade symfony/mime to v4.3.8 #805
* Bump handlebars to version 4.3.1 #761
* Upgrade handlebars to 4.5.1 #805

## 5.13.3
This is a security release that will harden the application against CVE 2019-3465
* Implement countermeasures against CVE 2019-3465 #802

## 5.13.2
A missing feature was implemented

* Add missing trusted proxy to stepup callout #778


## 5.13.1
* Hotfix use of coins in consent template #756


## 5.13.0
Add stepup authentication to EB to be able to reap the benefits of the SFO functionality of the strong authentication stack.

 * Add stepup authentication #754


## 5.12.0
We've changed the way how coin entity properties are stored in the database. As of now, they will be stored in a serialized manner, in a single column. This greatly simplifies adding or removing coins in the future, as this no longer requires database schema changes.

 *  Store coin properties in a single column #752

## 5.11.3
The footer links have been made configurable to a greater extent in this release.

 * Make Wiki links configurable #736
 * Conditionally show a IdP support link on error pages #740

And in addition to those functional changes, the PDP error page has been restyled:

 * Improve PDP error screen styling #733

## 5.11.2
The 5.11.2 release was aimed at fixing visually unsound issues. And in addition the uncaught error page has been made reloadable.

 * Make uncaught error page reloadable #696
 * Increase language switch z-index on the error page #718
 * Hide language switch on 400 and 405 page #722
 * Decrease the surfboard offset on the error page #717

## 5.11.1
This release have all backported issues from release 5.10.2 and 5.10.3

## 5.11.0
The error feedback pages have been overhauled in this release. In addition, the theming solution that was previously used has been revised.

Be sure to read up on how to work with the new theming solution in the [README.md]()

The most notable changes are:
 * Grunt is no longer used, npm run scripts are used instead. #672
 * Theming has changed, allowing for more easy theme overrides for JS and Sass resources. [Wiki](https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines#theme-development)
 * The error page styling has been updated, making it more user friendly by being focused on the important information instead of overflowing the user with irrelevant information.
 * The error pages have been made mobile friendly #673
 * Minified front-end assets are no longer kept in version control #667
 * Visual regression tests have been added for the error pages #687
 * The browser support policy has been made explicit #686

## 5.10.4
This release changed the way the rolling upgrades are handled between version 5.9 and 5.10. In code we stop using two
database columns we no longer use. But the database migration that accompanies that change was also included in this
release. This change was removed in this release and will be executed in 5.11.

See #704 for details.

## 5.10.3
The unsolicited SSO endpoint received some TLC. In addition to adding request input validation, we also added additional test coverage and cleaned up some unused routes.

* Enrich unsolicited SSO test coverage #699
* Unsolicited request validation #700
* Remove unused SSO routes #701

## 5.10.2

Two regressions where fixed in this release:

 * Whitelist debugSingleSignOnService for access without session #697
 * Remove validation on unsolicitedSingleSignOnAction #698

## 5.10.1

### Features
 * Add the possibility to sign a SAML response #690

## 5.10.0

The 5.10 release focuses on making EngineBlock more robust. The biggest aim is to improve error reporting.

### Features
 * SSO and ACS mis-use is logged earlier in the process #641 #642
 * Show a repeatable error code on the custom error pages (ART) #645
 * Improved handling of HTTP 405 request errors #646 #647
 * Allow re-posting of SAML Responses, allowing page refresh on Consent screen #648
 * Allow for more than one in-flight authentication session per user session #649
 * Added further support for Portuguese language #650 (thanks @domgon!)
 * Cleanup rolling update 5.8 compatibility support #683 (this was added in 5.8.3)
 * Update release script #666 #681
 * Differentiate session exception #657
 * Add Saml binding validator to acs endpoint #665
 * Make the memory limit for push configurable #680
 * Make the metadata push action more efficient #679

## 5.9.4

### Bugfix
 * Prevent blurring the search bar #656
 * Make sure the mouse responds after removing previous IdP selection #661
 * Unconnected IdP mouse hover should also highlight the IdP #662

## 5.9.3

### Bugfix
 * Honor explicit keyid in unsolicited single sign on #653

## 5.9.2
This release improves upon some of the previous consent changes

 * Display the no_attributes text in the IdP table #637
 * Apply XS viewport UI improvements #638

But also adds Jest unit and smoke tests for the WAYF

 * Provide JS smoke tests for the WAYF #635

## 5.9.1

### Bugfix
 * Filter non string values from AA response #622 #636

## 5.9.0
Most effort for this release was invested in upgrading Symfony to version 3.4.

### Features
 * Portuguese language support and other language related work #615 #612 thanks @domgon and @tvdijen!
 * Allow selecting unconnected IdP's with keyboard #634
 * Stop releasing the SURFconextId attribute #631

### Improvements
 * Replace un needed polyfills #606 thanks @BackEndTea
 * Add an editorconfig #607 thanks @BackEndTea
 * Harden against hypothetical XXE vulnerability #621
 * Several WAYF improvements: #623 #624 #625 #626 #627 #628 #629 #630
 * Symfony 3.4 upgrade (#617 #614)

### Bugfix
 * Fix displayName on WAYF #603 thanks @tvdijen!

## 5.8.6
**Bugfix**: Stop overwriting the NameId before giving consent #610

## 5.8.5
Bugfix for a possible break after giving consent. This release is preventing a crash when the original issuer is null #604

## 5.8.4
A security patch, fixing a possible XSS vulnerability.
Described in more detail in #598

## 5.8.3
This is a release mainly focused on the rolling updates. Be aware that 5.8 releases prior to 5.8.3 do have some
breaking changes in migrations due to the rolling update implementation added in this release . In order to update you
should skip releases <5.8.3.

### Chores and other improvements
 * Symfony was upgrade from version 2.8 to 3.4. This required the upgrade of quite some other (mainly dev) dependencies. #590

### Features
 * A custom database health check is added for the Monitor bundle. #589
 * A feature toggle to disallow users on attribute violations is added. #591
 * Add Rolling update support #595

## 5.8.2
This is mainly a release that consists of fixes of technical debt, longer standing quirks and other maintenance related
features.

### Features
 * Add CodeStyle fixer to the project #583

### Bugfixes
 * Optimize consent viewport on xs #573
 * Revert suggestion title on WAYF screen #571
 * Fix SP displayName regression #568 (thanks tvdijen)
 * Update the IdP placeholder logo reference #574
 * Prevent adding empty 'return' hidden input field #584

### Chores and other improvements
 * References to Janus have been removed #581
 * Remove attribute_aggregation_required metadata setting #572
 * Symfony was upgraded to 2.8.44 to harden against CVE-2018-14773 #582
 * Add requesterid_required metadata setting to enforce the use of a RequesterId on trusted proxies (#540)
