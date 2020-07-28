# Engineblock configurable AuthnContextClassRef

It's possible to configure an `AuthnConextClassRef` for a given SP-IdP combination, such that EngineBlock when processing a login for that SP, will request this string in the AuthnRequest to the IdP, and verify that it is returned in the assertion from the IdP - or show an error otherwise.

The following `coin` metadata attributes  need to be passed from the Manage (or other software) instance that is pushing EngineBlock metadata into EngineBlock.


## Engineblock metadata configuration

### IdP
#### metadata:coin:mfa_entities

**Type:** object
* name: _entityId_,
* level: _authncontextclassref_

An entry per SP which should use the configured AuthnContextClassRef.
