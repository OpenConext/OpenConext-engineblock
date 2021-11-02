# SSO Session Cookie

As a Service Provider I want to be able to discover whether a user can log in or is logged in via Engineblock,
so that I can optimize the start of the authentication process.

When the user is successfully authenticated by the Identity Provider, Engineblock must store an SSO session cookie in
their browser. With this cookie it is possible, if necessary, to verify whether the user has been successfully
authenticated with at least one Identity Provider.

An authentication with the Identity Provider is successful when the SAML response contains:

    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success" />
    </samlp:Status>

The value of the cookie contains an array of the entity ID('s) of the successfully authenticated Identity Providers.
This cookie will be deleted at `/logout` or will expire according to the set `sso_session_cookie_max_age`.
If the SSO Cookie Session feature is enabled it is also possible to manually delete this cookie at
`/authentication/idp/remove-cookies`. Do note however, that the remove cookies endpoint is only available if
`wayf.remember_choice=true`

## Configuration of SSO Session Cookie in Engineblock

SSO Session Cookie is an optional feature and can be enabled with:

    feature_enable_sso_session_cookie: true

The time the cookie expires is configurable. This is a timestamp in number of seconds after the authentication.
If set to 0 the cookie will expire at the end of the session (when the browser closes).

    sso_session_cookie_max_age: 0

Setting the SSO Session Cookie requires the path and domain to be correctly configured:

    cookie.path: /
    cookie.locale.domain: .vm.openconext.org

If the domain does not match, many browsers will reject the cookie.
