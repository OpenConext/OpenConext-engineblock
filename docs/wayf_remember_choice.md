# WAYF remember my choice

EngineBlock can remember which Identity Provider (IdP) a user picked on the WAYF, so that the user skips the WAYF on a
returning visit. There are two ways to do this. They are **mutually exclusive**: you enable one or the other, never
both.

| | Global mode | Per-SP mode |
|---|---|---|
| Parameter | `wayf.remember_choice` | `feature_enable_wayf_remember_choice_per_idp` |
| Scope of the remembered choice | One IdP for all SPs | One IdP per SP |
| SP opt-in required | No, applies to all SPs | Yes, SP metadata coin `coin:wayf_remember_choice` |
| Cookie name | `rememberchoice` | `rememberedidps` |
| Written by | Browser (JavaScript) | EngineBlock (server side) |
| Cookie lifetime | 365 days | `wayf.remember_choice_per_idp_lifetime` (default 90 days) |
| Cookie attributes | Secure, SameSite=None | Secure, HttpOnly, SameSite=None, scoped to the EngineBlock host |

## Choosing a mode

Enable at most one of the following in `config/packages/parameters.yml`:

    # Global mode
    wayf.remember_choice: true
    feature_enable_wayf_remember_choice_per_idp: false

or

    # Per-SP mode
    wayf.remember_choice: false
    feature_enable_wayf_remember_choice_per_idp: true

If both are `false`, the WAYF has no remember my choice checkbox and EngineBlock never skips the WAYF based on an
earlier choice.

### Both modes enabled is an error

If both parameters are `true`, EngineBlock refuses to build its container and fails with:

    The WAYF remember-my-choice modes are mutually exclusive: "wayf.remember_choice" and
    "feature_enable_wayf_remember_choice_per_idp" cannot both be true. Enable only one of them.

The check runs when the Symfony container is compiled, so a wrong configuration is caught by `cache:clear` or
`cache:warmup` during deployment, before any user is affected. Fix the configuration by setting one of the two
parameters to `false`.

The per-SP mode does not depend on `wayf.remember_choice`. Earlier development versions required both to be `true`;
that combination is now rejected.

## Global mode

With `wayf.remember_choice: true` the WAYF shows a "remember my choice" checkbox. When the user checks it and picks an
IdP, the browser stores the IdP entity ID in the `rememberchoice` cookie. On the next authentication request,
EngineBlock skips the WAYF and sends the user to that IdP if it is a candidate IdP for the requesting SP. The choice is
shared by all SPs.

The remembered IdP is ignored for `ForceAuthn` and debug requests.

The cookie can be removed on `/authentication/idp/remove-cookies`, see [Removing the cookies](#removing-the-cookies).

## Per-SP mode

With `feature_enable_wayf_remember_choice_per_idp: true` the remembered choice is stored per SP. The feature is
opt-in per SP through Manage metadata:

    metadata:coin:wayf_remember_choice: true

Only the boolean `true` (or `"1"`) opts an SP in. An SP without the coin never uses the per-SP cookie, and never falls
back to the global cookie.

When a user checks the checkbox and picks an IdP for an opted-in SP, EngineBlock stores the `(SP, IdP)` pair in the
`rememberedidps` cookie. On the next authentication request from that SP, EngineBlock skips the WAYF if the remembered
IdP is still a candidate IdP. The one-IdP shortcut and the SSO notification shortcut always take precedence, and
`ForceAuthn` and debug requests ignore the remembered IdP.

Related parameters:

    # Lifetime of a remembered choice, in seconds (7776000 seconds = 90 days)
    wayf.remember_choice_per_idp_lifetime: 7776000

    # Maximum number of remembered SPs per cookie. The entries that expire soonest are evicted first.
    wayf.remember_choice_per_idp_max: 16

The cookie content is validated strictly. Invalid or expired entries are never used to select an IdP and are removed
from the cookie.

The cookie can be removed on `/authentication/idp/remove-cookies`, see [Removing the cookies](#removing-the-cookies).

## Removing the cookies

The page `/authentication/idp/remove-cookies` lets a user inspect and remove the cookies EngineBlock has set,
including `rememberchoice` and `rememberedidps`. The page is available when either mode is enabled, and returns a 404
when both are disabled.

## Switching modes

Cookies written in one mode are not read in the other. After switching, users have to make their choice once more.
Switching from global to per-SP mode requires that you first set `wayf.remember_choice` to `false`, and then enable
`feature_enable_wayf_remember_choice_per_idp` and the `coin:wayf_remember_choice` coin on the SPs that should
use it.
