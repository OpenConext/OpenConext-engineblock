# CHANGELOG
All release notes between version 4.7.5 and 5.8.0 can be found on [GitHub](https://github.com/OpenConext/OpenConext-engineblock/releases). Release notes for version <= 4.7.5 are in the repository under `docs/release_notes`. 

We will continue to post relevant release notes on the GitHub release page. More detailed release notes should be placed in this document. 

More information about our release strategy can be found in the [Development Guidelines](https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines#release-notes) on the EngineBlock wiki.

## 6.1.0
The metadata endpoints of EngineBlock have been under heavy maintenance in this release. Some highlights include the
move of all generation logic to the new Symfony EngineBlock application. But maybe more important, EngineBlock no longer
relies on entity information from the `sso_provider_roles_eb5` table. All EngineBlock metadata is either loaded from
ini config, or is hardcoded into the application.

**Other chores**
* Move the dev cache location outside the project root #780 

## 6.0.0
In this release, PHP 5.6 support was dropped in favour of PHP 7.2. We are not migrating to the latest PHP version for some reasons. Chief amongst which is that PHP 7.2 was best compatible with the current EngineBlock code base. 
An upgrade to 7.3 or even 7.4 would force us to upgrade many third party dependencies at the same time, making this release much bigger.

For installation instructions, see the UPGRADING.md entry for this version.

The following changes where introduced in this release:

**Improvements**
* PHP 7.2 compatibility changes #713
* Prevent WAYF button from floating left #760

**Bugfix**
* Get termsOfServiceUrl from coins, not SP metadata entity #756

**Other chores**
* Cleanup old coin columns #755
* Bump handlebars to version 4.3.1 #761
* Update Composer settings for improved PHP 7.2 support #763
* Update testing tools (PHPUnit, Mockery & Phake) #777


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
