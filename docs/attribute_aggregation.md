# Engineblock Attribute Aggregation

By default the user's attributes are released by their IdP, then filtered
by Engineblock in the Attribute Release Policy (ARP), and released to the SP.

Attribute Aggregation gives the ability to add attributes for a user from
a third party source and release them to the SP in the same set as the
original IdP attributes.

The actual interfacing with these external sources (attribute authorities)
is done via the external
[OpenConext-attribute-aggregation](https://github.com/OpenConext/OpenConext-attribute-aggregation)
service (or anything that provides the same API).


## How to configure it

In `engineblock.ini`, point Engineblock to your instance of the
AA with the settings:
```ini
attributeAggregation.baseUrl = "https://aa.<example.org>/aa/api/attribute/aggregate"
attributeAggregation.username = "eb"
attributeAggregation.password = "secret"
```

To use Attribute Aggregation, you configure it in
Serviceregistry on the SP side.

* In the ARP for the SP, each attribute has a 'source'
column, defauling to IdP. When set to a different value,
that attribute will be sourced from the Attribute Aggregator.

## How it works

When handling a login, after the user is returned from the
IdP with their initial attribute set, the [Attribute
Aggregator Client](https://github.com/OpenConext/OpenConext-engineblock/blob/master/library/EngineBlock/Corto/Filter/Command/AttributeAggregator.php) in Engineblock is run. This client sends
the ARP of this SP including the source information, and
all the user's attributes from their IdP, to the Attribute
Aggregator.

The Attribute Aggregator knows for each source which
input attributes it requires and how to query that source.
It can also use a specified value filter from the ARP if present.
It will return the resulting attributes to Engineblock.

Engineblock will always _replace_ the contents of any
attribute that has a source specified with the contents
from the attribute aggregator. That means that if
aggregation is enabled for attribute _x_, any value
received from the IdP for _x_ is discarded and
only what is received from the AA for _x_ is sent on
to the IdP (even when the result from the AA for _x_
is empty, the SP will hence also not receive any value
for _x_; the attribute will be omitted).

## Adding new sources

New sources are added in the Attribute Aggregator,
according to the instructions in its README. In order
for it to be configurable in Manage, add it
to the list of
[`janus_attribute_sources`](https://github.com/OpenConext/OpenConext-deploy/blob/2b6e5ef385ec41ceba58d271be049fb6a17b06ac/environments/docker/group_vars/docker.yml#L115-L116).
Engineblock itself is agnostic of which sources exist
and does not require additional configuration to add
a source.
