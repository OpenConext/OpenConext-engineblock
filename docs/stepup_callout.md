# Engineblock Stepup second factor integration

It's possible to require second factor authentication in EngineBlock using OpenConext StepUp in second factor only (SFO) mode. When configured correctly, EngineBlock will utilize StepUp Gateway's SFO to do a second factor callout with a given minimum Level of Assurance (LoA).

Currently the following configrations are supported:
* On a per-sp basis: configure the required LoA in Manage which will be enforced for all logins to this SP by any IdP.
* On a idp-sp basis: configure the required loa in Manage for an IdP + a specific SP. All users from this IdP will for logging into this SP require a token with this LoA.
* Based on PDP rules. The policy enforcement point in Engineblock can interpret decisions from the PDP that specify a minimum LoA.

Engineblock picks the highest of any LoA configured in these places to enforce.

## Engineblock metadata configuration
### SP
#### `metadata:coin:stepup:allow_no_token`
**Type:** boolean

To continue with LOA 1 if no second factor token found. This goes for all
modes of LoA requirement, also per IdP-SP and via the PDP.

Note that this mode is very risky; the associated SP needs to do something with the resulting AuthnContextClassRef and take further measures based on the attained LoA level of the user.

#### `metadata:coin:stepup:requireloa`
The LOA minimally required for any login to this SP.

**Type:** string
**Example value:** `http://vm.openconext.org/assurance/loa2`


#### `metadata:coin:stepup:forceauthn`
**Type:** boolean

Set the ForceAuthn flag in the callout authentication request to the Stepup Gateway for any authentication
that involves this service provider. The meaning of the flag to the Stepup Gateway is to disable Single
Sign On on the second factor for this request.


### IdP
#### `metadata:coin:stepup_connections`

**Type:** object
* name: _entityId_,
* level: _requiredLoa_

An entry per SP which requires the SFO capabilities.

## PDP

The OpenConext policy decision point (PDP, minimal version 3.0.0) allows to specify Stepup rules which will be evaluated by the PDP. The PDP is only invoked for an SP that has `coin:policy_enforcement_decision_required` set to true. Inputs for this decision provided by EB to the PDP are the IdP, the SP, user's attributes and their client IP address.

The PDP will return a minimally required LoA for each matching ruleset. These rules can be combined with the PDP allow/deny rules.

## Engineblock global configuration
The EngineBlock installation also needs additional configuration in order to facilitate the SFO second factor authentications. For details on these configuration settings, please review the SFO section in the [app/config/parameters.yml.dist](app/config/parameters.yml.dist) file.

## Development and testing
To test this integration in EngineBlock, you can configure one of the SP's known to your installation (example `profile.vm.openconext.org`) to require step up authentication. After pushing the updated metadata back to EngineBlock, Engine should start asking for step up authentications when logging in to the SP. Note that a mock StepUp SFO endpoint is implemented in EB when in `dev` or `test` mode. That mimics the SFO callout to the StepUp Gateway. Allowing developers and testsers to modify the behaviour of the Gateway response without actually having to run a StepUp instance.
