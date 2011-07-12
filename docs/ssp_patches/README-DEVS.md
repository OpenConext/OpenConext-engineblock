SimpleSamlPHP Patch Readme
==========================

This file lists the patches for SimpleSAMLphp. The patches are already applied to the serviceregistry since SSP is not
an external. If you create a new patch be sure to also submit it to the SSP community!

If the patch has been accepted and we upgrade to a new version of SSP we can remove the patch.

List of Patches
---------------

- Make SimpleSAMLAuthToken cookie name configurable

### Make SimpleSAMLAuthToken cookie name configurable ###

This patch makes the SimpleSAMLAuthToken cookie name configurable. This is important when running multiple SSP
instances in different subdomains. When the SimpleSAMLAuthToken cookie is set by the first instance of SSP the
second instance of SSP will fail since the AuthToken is not valid for the second instance.

 __Patch file:__

    make_SimpleSAMLAuthToken_cookiename_configurable.patch
