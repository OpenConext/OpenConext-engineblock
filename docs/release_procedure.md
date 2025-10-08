# Release Procedure

The following steps should be taken when creating a new release:
- Verify the last commit on main has a successful build.
- Make sure the changelog is up to date, and all changes since the previous release are included.
- Verify that a release based on main can be built:
  ```
  git clone git@github.com:OpenConext/Stepup-Build.git
  cd Stepup-Build
  ./stepup-build.sh OpenConext-engineblock --branch main
  ```
- Tag a new release in the repository and push it to Github. The tag name should be the version number.
- Wait for the release build to complete.
-  Create the release in the Github interface: open the tagged version and copy the changelog to the release
- Communicate the latest release.

All done.
