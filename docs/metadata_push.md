# Engineblock metadata storage and push

EngineBlock has its own local database table that stores the remote SP and IdP entities
it knows about: `sso_provider_roles_eb5`. This table contains the entities in a ready
to consume format so logins can be handled quickly and without external dependencies
on other services or APIs.

## Push connections

To fill this table and keep it up to date, the EngineBlock API (engine-api vhost)
has a `/api/connections` endpoint. The tool that administers the known entities
(OpenConext-manage) can push an update to this endpoint with the new state of
all entities. Engine will process this set and updates its internal table appropriately
(incrementally, thus without downtime).

## Push contents

The API accepts a HTTP POST of a JSON hash with the complete state of all remote entities
in it. OpenConext Manage implements this. See
[the Manage wiki(https://github.com/OpenConext/OpenConext-manage/wiki/Push-Metadata)
for some implementation details of the push on the manage side.

A push with 0 entities will be rejected to prevent mistakes from clearing out the database.

## Push api configuration and constraints.

The push API is enabled via the `api.metadata_push` feature flag and requires
HTTP Basic Authentication by a user with the rights
`ROLE_API_USER_METADATA_PUSH`.  A memory_limit can be set in the configuration
to prevent the processing of the incoming entities set to run out of memory via
the setting `engineblock.metadata_push_memory_limit`.  This is all configured
under EngineBlock's `app/config/` dir.
