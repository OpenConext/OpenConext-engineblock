# CHANGELOG
All release notes between version 4.7.5 and 5.8.0 can be found on [GitHub](https://github.com/OpenConext/OpenConext-engineblock/releases). Release notes for version <= 4.7.5 are in the repository under `docs/release_notes`. 

We will continue to post relevant release notes on the GitHub release page. More detailed release notes should be placed in this document. 

More information about our release strategy can be found in the [Development Guidelines](https://github.com/OpenConext/OpenConext-engineblock/wiki/Development-Guidelines#release-notes) on the EngineBlock wiki.

## Development

### Features
 * A custom database health check is added for the Monitor bundle. #589
 * A feature toggle to disallow users on attribute violations is added. #591
 * A custom database health check is added for the Monitor bundle. #589 

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
