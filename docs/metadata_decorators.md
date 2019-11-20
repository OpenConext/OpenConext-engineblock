# EngineBlock SP/IdP decorators

## `IdentityProviderEntityInterface` & `ServiceProviderEntityInterface`
This interface describes all getters that should be available on the decorator implementations. This are all getters
that are available on a SP / IdP entity. And is based on the public properties of the Doctrine ORM SP/IdP entities.

## `IdentityProviderEntity` & `ServiceProviderEntity` adapters

**Description**

The adapters encapsulate the ORM entities, making them immutable.

**Usage**

The adapters are used at the core of the decorator creations. For example see the creation of the EngineBlock IdP entity
in the `IdentityProviderFactory`

```php
private function buildEngineBlockEntityFromEntity(IdentityProvider $entity, string $keyId): IdentityProviderEntityInterface
{
    return new EngineBlockIdentityProvider(  
        new EngineBlockIdentityProviderInformation(
            new IdentityProviderEntity($entity) // <-- The adapter is instantiated with $entity (Doctrine entity), encapsulating it.
        )
    );
}
```

## `AbstractIdentityProvider` & `AbstractServiceProvider`

**Description**

This abstract class is used to circumvent the implementation of all methods of the IdentityProviderEntityInterface.
So only the methods required for the specific implementation have to be created on the decorated Entity that is
extended from this abstract IdP entity.

**Usage**

The specific decorators extend these abstract classes. For example the EngineBlockIdentityProviderInformation class does
so:

```php
class EngineBlockIdentityProviderInformation extends AbstractIdentityProvider
{
...
}
``` 

The `AbstractIdentityProvider` & `AbstractServiceProvider` are actually `abstract` classes and can not be used directly.

## `EngineBlockIdentityProvider` & `EngineBlockServiceProvider`

**Description**

Decorators specifically used to represent EngineBlock in its role as IdP and SP respectively. For now only used to 
render SAML Metadata. Tasked with returning the correct certificate, name id formats and SSO/SLO locations. These are 
tailormade for EngineBlock.

The certificates are based on the configured certificates in the `engineblock.ini`. And the key specified on the 
metadata endpoint is used. 

The NameIDFormats are hardcoded.

And finally the SSO and SLO locations are constructed based on the hard coded supported bindings and the routes 
specified in the routing YAML configuration. 

**Usage**

Example usage as found in the `IdentityProviderFactory`:
```php
private function buildEngineBlockEntityFromEntity(IdentityProvider $entity, string $keyId): IdentityProviderEntityInterface
{
    return new EngineBlockIdentityProvider( 
        new EngineBlockIdentityProviderInformation( 
            new IdentityProviderEntity($entity),
            $this->engineBlockConfiguration
        ),
        $this->keyPairFactory->buildFromIdentifier($keyId),
        $this->urlProvider
    );
}
```


## `EngineBlockIdentityProviderInformation` & `EngineBlockServiceProviderInformation`

**Description**

Provides the hard coded or configured EngineBlock information. Based on a `EngineBlockConfiguration` instance. This
includes: `DisplayName`, `Description`, `ContactPersons`, `Logo`, and the `Organization`

**Usage**

Example usage as found in the `IdentityProviderFactory`:

```php
private function buildEngineBlockEntityFromEntity(IdentityProvider $entity, string $keyId): IdentityProviderEntityInterface
{
   $engineBlockConfiguration = new EngineBlockConfiguration();
   return new EngineBlockIdentityProvider(  
       new EngineBlockIdentityProviderInformation( // <-- Creation of the EngineBlockIdentityProviderInformation
           new IdentityProviderEntity($entity),
           $engineBlockConfiguration // <-- The configuration is provided as the second parameter
       )
   );
}
```

## `ProxiedIdenityProvider`

**Description**

Used to represent an IdP that is proxied by EngineBlock.

Of these IdPs, certain metadata is overridden:
 - The EB certificate is used;
 - The EB SSO/SLO location is used (with addition of an identifier to later identify which IdP was chosen);
 - NameIdFormats of EB are used;
 - ContactPersons of EB are used.

**Usage**

Example usage as found in the `IdentityProviderFactory`:

```php
private function buildIdentityProviderFromEntity(IdentityProvider $entity, string $keyId): IdentityProviderEntityInterface
{
    // Set IdP specific properties where the IdP is proxied by EngineBlock. So the EB certificate, contact persons
    // and SSO location are overridden
    return new ProxiedIdentityProvider( 
        new IdentityProviderEntity($entity),
        $this->engineBlockConfiguration,
        $this->keyPairFactory->buildFromIdentifier($keyId),
        $this->urlProvider
    );
}
```

## `ServiceProviderStepup`

**Description**

EngineBlock as of release 5.13 provides step-up authentication as a feature. And it specifies SP metadata that the 
step-up authentication provider can send its SAML Response to.

This decorator is used to render step-up authentication SP SAML Metadata.  

**Usage**

```php
public function createStepupEntityFrom(string $keyId): ServiceProviderEntityInterface
{
    return new ServiceProviderStepup(
        new ServiceProviderEntity($entity),
        $this->keyPairFactory->buildFromIdentifier($keyId),
        $this->urlProvider
    );
}
```
