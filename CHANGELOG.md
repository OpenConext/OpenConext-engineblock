# CHANGELOG
All release notes between version 4.7.5 and 5.8.0 can be found on [GitHub](https://github.com/OpenConext/OpenConext-engineblock/releases). Release notes for version <= 4.7.5 are in the repository under `docs/release_notes`. 

We will continue to post relevant release notes on the GitHub release page. More detailed release notes should be placed in this document. 

More information about our release strategy can be found in the [Development Guidelines](https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines#release-notes) on the EngineBlock wiki.

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

### Chores and other improvements
 * Symfony was upgrade from version 2.8 to 3.4. This required the upgrade of quite some other (mainly dev) dependencies. #590

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
