# CHANGELOG
All release notes between version 4.7.5 and 5.8.0 can be found on [GitHub](https://github.com/OpenConext/OpenConext-engineblock/releases). Release notes for version <= 4.7.5 are in the repository under `docs/release_notes`. 

We will continue to post relevant release notes on the GitHub release page. More detailed release notes should be placed in this document. 

More information about our release strategy can be found in the [Development Guidelines](https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines#release-notes) on the EngineBlock wiki.

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
