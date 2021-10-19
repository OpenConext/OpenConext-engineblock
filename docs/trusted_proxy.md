# Trusted Proxy in EngineBlock

In version 4.3.3 support for "trusted proxies" was added to Openconext. A "trusted proxy" is a SAML SP that uses engineblock as its IdP. The difference between a normal SP and a SP is that is a trusted proxy is that the latter is allowed to impersonate other service providers. This allows a SP proxy to be connected to engineblock while using the ACL, ARP, consent and pseudonymisation functions of engineblock.


## Enabling

Trusted proxy is enabled per SP. To enable trusted proxy behaviour for a SP, set "coin:trusted_proxy" and "redirect.sign" to true for the configuration of the SP. The default for this setting is false. Note that after enabling "redirect.sign" all AuthnRequests must be signed.

## Engineblock Trusted Proxy behaviour

Engineblock will only enable trusted proxy processing for a SAML AuthnRequest that it receives from a SP when all of the following conditions are met:
* Both "coin:trusted_proxy" and "redirect.sign" are set in the SP Entity configuration in engineblock. This SP is identified by the value of the /AuthnRequest/Issuer element in the SAML AuthnRequest.
* The AuthnRequest has a valid signature
* The SAML AuthnRequest contains at least one /AuthnRequest/Scoping/RequesterID element.

### Trusted proxy processing

The image below shows a proxy (a trusted proxy) that is connected to engineblock :
* The __trusted proxy SP__ is an SP that sends an AuthnRequest to engineblock.
* The __SP being proxied__ is the SP behind the trusted proxy.

![trusted_proxy](trusted_proxy.png)

When processing a AuthnRequest from a trusted proxy engineblock performs some actions as if the SP being proxied sent the AuthnRequest directly. This is what differentiates trusted proxy processing from the normal processing in engineblock, which is also proxy aware but will never allow impersonation of another SP or reveal pseudonyms of another SP. For trusted proxies engineblock:
* Generates the NameID for the SP being proxied and adds this to the eduPersonTargetedID attribute. This makes the persistent NameID (which is a pseudonym targeted at a SP) of the SP being proxied available to the trusted proxy. The NameID for the trusted proxy is available in the Subject in the Assertion. This way the trusted proxy can have an unique identifier for the user. When generating the Assertion for the SP being proxied the trusted proxy must copy the NameID from the eduPersonTargetedID to the Subject.
* Consent is asked and remembered for the SP being proxied
* The attribute manipulations of the SP being proxied are run

### Trusted proxy processing details

The SAML AuthnRequest below is en example of the AuthnRequest that a trusted proxy sends to engineblock. In this request:
* The trusted proxy SP has an entityID of "https://trusted-proxy.example.com/metadata". This is the value of the `/AuthnRequest/Issuer` element in the SAML AuthnRequest to engineblock.
* The SP being proxied has en entityID of "https://sp-being-proxied.example.net/metadata". Engineblock uses the value of the _last_ `/AuthnRequest/Scoping/RequesterID` element in the SAML AuthnRequest to engineblock. This means that a trusted proxy must ensure that its RequesterID element is the last RequesterID in the list.

```xml
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="_7a531831ec3d4b26f594d5bfcd676d6295d8ceb4ef5b8742c8b6f39a2a11"
                    Version="2.0"
                    IssueInstant="2017-07-27T14:18:39Z"
                    Destination="https://eb.example.org/authentication/idp/single-sign-on"
                    AssertionConsumerServiceURL="https://trusted-proxy.example.com/consume-assertion"
                    ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST">
    <saml:Issuer>https://trusted-proxy.example.com/metadata</saml:Issuer>
    <samlp:Scoping ProxyCount="10">
        <samlp:RequesterID>https://sp-being-proxied.example.net/metadata</samlp:RequesterID>
    </samlp:Scoping>
</samlp:AuthnRequest>
```

Processing of the request:
* Both the trusted proxy SP and the SP being proxied must be known to engineblock
* Both the trusted proxy SP and the SP being proxied must have the same workflow state

* The ACL of both the trusted proxy SP and the SP being proxied are verified. Only IdPs are allowed access to both SPs are allowed to login
* The ARPs of both the trusted proxy SP and the SP being proxied are applied. Only attributes and attribute values that are allowed by both ARP are included in the response
* The attribute manipulations (AMs) of both the trusted proxy SP and the SP being proxied are run. The AMs of the trusted proxy SP are run first.
* The PDP is called for the SP being proxied only.

* The NameID in the Subject of the response is generated according the NameID configured for the trusted proxy SP. The NameID format requested in the AuthnRequest is not taken into account.
* The NameID in the eduPersonTargetedID attribute of the response is generated according the NameID configured for the trusted proxy SP. The NameID format requested in the AuthnRequest are not taken into account.
