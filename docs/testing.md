# Testing

## WAYF functional-testing page

The functional-testing route renders the WAYF page with synthetic IdP data, controllable via query parameters. Use it for manual verification and as the base URL for Cypress tests.

**Base URL:** `https://engine.dev.openconext.local/functional-testing/wayf`

### Query parameters

| Parameter | Type | Default | Description |
|---|---|---|---|
| `lang` | string | `en` | Locale (`en` or `nl`) |
| `connectedIdps` | int | `5` | Number of connected IdPs to generate |
| `unconnectedIdps` | int | `0` | Number of unconnected IdPs to generate |
| `randomIdps` | int | `0` | Generate N IdPs with random (Faker) names instead; overrides connected/unconnected |
| `addDiscoveries` | bool | `true` | Add discovery entries to IdP 1 (gives it 3 list entries instead of 1) |
| `preferredIdpEntityIds[]` | string[] | `[]` | Entity IDs to feature in the preferred section (array syntax required) |
| `defaultIdpEntityId` | string | - | Entity ID of the default IdP (shows banner) |
| `showIdPBanner` | bool | `true` | Whether to show the default IdP banner |
| `displayUnconnectedIdpsWayf` | bool | `false` | Show unconnected IdPs with a "Request access" button |
| `backLink` | bool | `false` | Show "Return to service provider" back link |
| `rememberChoiceFeature` | bool | `false` | Show "Remember my choice" checkbox |
| `cutoffPointForShowingUnfilteredIdps` | int | `100` | Hide the IdP list until the user searches when list length exceeds this value |

#### Baseline
- [Default (5 connected IdPs)](https://engine.dev.openconext.local/functional-testing/wayf)
- [Dutch locale](https://engine.dev.openconext.local/functional-testing/wayf?lang=nl)
- [10 IdPs](https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=10&addDiscoveries=false)
- [Random IdPs (Faker names)](https://engine.dev.openconext.local/functional-testing/wayf?randomIdps=8)

#### Cutoff / search
- [Cutoff active - list hidden until search](https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=6&cutoffPointForShowingUnfilteredIdps=5)

#### Unconnected IdPs / request access
- [Unconnected IdPs visible, no request access](https://engine.dev.openconext.local/functional-testing/wayf?unconnectedIdps=3)
- [Unconnected IdPs + request access button](https://engine.dev.openconext.local/functional-testing/wayf?unconnectedIdps=3&displayUnconnectedIdpsWayf=true)

#### UI features
- [Back link](https://engine.dev.openconext.local/functional-testing/wayf?backLink=true)
- [Remember my choice](https://engine.dev.openconext.local/functional-testing/wayf?rememberChoiceFeature=true)
- [Default IdP banner](https://engine.dev.openconext.local/functional-testing/wayf?defaultIdpEntityId=https%3A%2F%2Fexample.com%2FentityId%2F3&showIdPBanner=true&addDiscoveries=false)

#### Preferred IdPs
- [1 preferred IdP](https://engine.dev.openconext.local/functional-testing/wayf?preferredIdpEntityIds%5B%5D=https%3A%2F%2Fexample.com%2FentityId%2F1&addDiscoveries=false)
- [2 preferred IdPs](https://engine.dev.openconext.local/functional-testing/wayf?preferredIdpEntityIds%5B%5D=https%3A%2F%2Fexample.com%2FentityId%2F1&preferredIdpEntityIds%5B%5D=https%3A%2F%2Fexample.com%2FentityId%2F2&addDiscoveries=false)
- [Preferred = default IdP > banner suppressed](https://engine.dev.openconext.local/functional-testing/wayf?preferredIdpEntityIds%5B%5D=https%3A%2F%2Fexample.com%2FentityId%2F1&defaultIdpEntityId=https%3A%2F%2Fexample.com%2FentityId%2F1&showIdPBanner=true&addDiscoveries=false)
- [Preferred ≠ default IdP > both visible](https://engine.dev.openconext.local/functional-testing/wayf?preferredIdpEntityIds%5B%5D=https%3A%2F%2Fexample.com%2FentityId%2F1&defaultIdpEntityId=https%3A%2F%2Fexample.com%2FentityId%2F2&showIdPBanner=true&addDiscoveries=false)
- [Preferred IdP with discoveries (1 IdP > 3 entries)](https://engine.dev.openconext.local/functional-testing/wayf?preferredIdpEntityIds%5B%5D=https%3A%2F%2Fexample.com%2FentityId%2F1)


- [All features enabled](https://engine.dev.openconext.local/functional-testing/wayf?connectedIdps=8&unconnectedIdps=2&displayUnconnectedIdpsWayf=true&preferredIdpEntityIds%5B%5D=https%3A%2F%2Fexample.com%2FentityId%2F1&defaultIdpEntityId=https%3A%2F%2Fexample.com%2FentityId%2F2&showIdPBanner=true&backLink=true&rememberChoiceFeature=true&addDiscoveries=false)
