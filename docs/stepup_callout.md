# Engineblock Stepup second factor integration

It's possible to require second factor authentication in EngineBlock using OpenConext StepUp in second factor only (SFO) mode. When configured correctly, EngineBlock will utilize StepUp Gateways SFO to do a second factor callout. In order to do so the EB configuration needs to be configured in a certain way to allow this on a per IdP or per SP basis. The following coin metadata attributes need to be passed from the Manage (or other software) instance that is pushing EngineBlock metadata into EngineBlock.


## Engineblock metadata configuration
### SP
#### metadata:coin:stepup:allow_no_token
**Type:** boolean

To continue with LOA 1 if no second factor token found

#### metadata:coin:stepup:requireloa
The LOA minimal required

**Type:** boolean




### IdP
#### metadata:coin:stepup_connections

**Type:** object
* name: _entityId_,
* level: _requiredLoa_

An entry per SP which should use the SFO capabilities.


## Engineblock global configuration
The EngineBlock installation also needs additional configuration in order to facilitate the SFO second factor authentications. For details on these configuration settings, please review the SFO section in the [app/config/parameters.yml.dist](app/config/parameters.yml.dist) file.

