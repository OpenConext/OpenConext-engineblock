# Release Procedure

The following steps should be taken when creating a new release:

1. Verify the last commit on master has a successful build.
1. Create an overview of which PRs have been merged to document in the release notes.  
   The following template can be used:
    ```
    This release includes everything up to and including {PREVIOUS_VERSION}, and adds the following changes:
    
    ### Functional Changes
    
    - {PR TITLE} #{PR NUMBER}
    
    ### Bugfixes, security patches and stability improvements
    
    - {PR TITLE} #{PR NUMBER}
    
    ### Minor improvements
    
    - {PR TITLE} #{PR NUMBER}
    ```
1. Verify that a release based on master can be built (using `bin/makeRelease.sh master`)
1. Create the release in the Github interface. Ensure the correct version number is used and use the prepared release
   notes.
1. Build a new release tarball based on the created tag using `bin/makeRelease.sh`  
   This creates two files: OpenConext-engineblock-{version}.tar.gz and OpenConext-engineblock-{version}.sha, both can be
   found in `~/Releases`.
1. Edit the release in the Github interface and upload the generated OpenConext-engineblock-{version}.tar.gz and
   OpenConext-engineblock-{version}.sha. Save the updated release.
1. Communicate the latest release.

All done.
