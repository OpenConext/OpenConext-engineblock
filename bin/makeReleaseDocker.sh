#!/bin/bash

PREVIOUS_SF_ENV=${SYMFONY_ENV}
PREVIOUS_EB_ENV=${ENGINEBLOCK_ENV}
export SYMFONY_ENV=prod
export ENGINEBLOCK_ENV=production

if [ -z "$1" ]
then
cat << EOF
Please specify the tag or branch to make a release of.

Examples:

    sh makeReleaseDocker.sh 0.1.0
    sh makeReleaseDocker.sh master
    sh makeReleaseDocker.sh develop

If you want to GPG sign the release, you can specify the "sign" parameter, this will
invoke the gpg command line tool to sign it.

   sh makeRelease 0.1.0 sign

EOF
exit 1
else
    TAG=$1
fi

RELEASE_DIR=/var/www/release
GITHUB_USER=OpenConext
PROJECT_NAME=OpenConext-engineblock
PROJECT_DIR_NAME=${PROJECT_NAME}-${TAG//\//_} &&
PROJECT_DIR=${RELEASE_DIR}/${PROJECT_DIR_NAME}

# Check requirements
command -v php >/dev/null 2>&1 || { echo >&2 "Missing PHP 7.2. Aborting"; exit 1; }
command -v composer >/dev/null 2>&1 || { echo >&2 "Missing Composer.  Aborting."; exit 1; }
command -v npm >/dev/null 2>&1 || { echo >&2 "Misisng NPM.  Aborting."; exit 1; }
command -v git >/dev/null 2>&1 || { echo >&2 "Misisng Git.  Aborting."; exit 1; }

# Prepare environment
echo "Preparing environment" &&
mkdir -p ${RELEASE_DIR} &&
rm -rf ${PROJECT_DIR} &&

echo "Cloning repository" &&
cd ${RELEASE_DIR} &&
git clone https://github.com/${GITHUB_USER}/${PROJECT_NAME}.git ${PROJECT_DIR_NAME} &&

echo "Checking out ${TAG}" &&
cd ${PROJECT_DIR} &&
git checkout ${TAG}

if [ $? -eq 0 ]; then
    echo "Project prepared"
else
    echo "Initialization failed"
    exit 1
fi


# Install composer dependencies
echo "Running Composer Install" &&
composer install -n --no-dev --prefer-dist -o

if [ $? -eq 0 ]; then
    echo "Composer install ran"
else
    echo "Unable to run compopser install"
    exit 1
fi

# Warmup cache
#php app/console cache:warmup --env=prod

# Build NPM frontend assets
echo "Build assets"
cd ${PROJECT_DIR}/theme &&
CYPRESS_INSTALL_BINARY=0 npm ci &&
npm run release

if [ $? -eq 0 ]; then
    echo "Assets build"
else
    echo "Unable to build assets"
    exit 1
fi

# Tag release and remove unwanted files
echo "Tagging the release in RELEASE file" &&
COMMITHASH=`git rev-parse HEAD` &&
echo "Tag: ${TAG}" > ${PROJECT_DIR}/RELEASE &&
echo "Commit: ${COMMITHASH}" >> ${PROJECT_DIR}/RELEASE &&

echo "Updating asset_version in config" &&
sed -i s,#ASSET_VERSION#,${TAG},g ${PROJECT_DIR}/app/config/config.yml &&


echo "Cleaning build of dev files" &&
rm -rf ${PROJECT_DIR}/.idea &&
rm -rf ${PROJECT_DIR}/.git &&
rm -f ${PROJECT_DIR}/.gitignore &&
rm -f ${PROJECT_DIR}/makeRelease.sh &&
rm -f ${PROJECT_DIR}/bin/composer.phar &&
rm -f ${PROJECT_DIR}/app_dev.php.dist &&
rm -rf ${PROJECT_DIR}/features &&
rm -rf ${PROJECT_DIR}/behat.yml &&
rm -rf ${PROJECT_DIR}/build.xml &&
rm -rf ${PROJECT_DIR}/tests &&
rm -rf ${PROJECT_DIR}/ci &&
rm -rf ${PROJECT_DIR}/theme/node_modules &&
rm -rf ${PROJECT_DIR}/theme/.sass-cache

if [ $? -eq 0 ]; then
    echo "Release build"
else
    echo "Failed to build release"
    exit 1
fi


# Create tarball
echo "Create tarball" &&
cd ${RELEASE_DIR} &&
tar -czf ${PROJECT_DIR_NAME}.tar.gz --exclude='release' ${PROJECT_DIR_NAME}

if [ $? -eq 0 ]; then
    echo "Tarball build"
else
    echo "Unable to build tarball"
    exit 1
fi


# Create checksum
echo "Create checksum file" &&
cd ${RELEASE_DIR} &&
if hash sha1sum 2>/dev/null; then
    sha1sum ${PROJECT_DIR_NAME}.tar.gz > ${PROJECT_DIR_NAME}.sha
else
    shasum ${PROJECT_DIR_NAME}.tar.gz > ${PROJECT_DIR_NAME}.sha
fi

if [ $? -eq 0 ]; then
    echo "Checksum created"
    cat ${PROJECT_DIR_NAME}.sha
else
    echo "Unable to create checksum"
    exit 1
fi


# Sign with GPG key
if [ -n "$2" ]
then
	if [ "$2" == "sign" ]
	then
	    echo "Signing build"
		cd ${RELEASE_DIR}
		gpg -o ${PROJECT_DIR_NAME}.sha.gpg  --clearsign ${PROJECT_DIR_NAME}.sha

        if [ $? -eq 0 ]; then
            echo "Signed"
        else
            echo "Unable to sign tarball"
            exit 1
        fi

	fi
fi

export SYMFONY_ENV=${PREVIOUS_SF_ENV}
export ENGINEBLOCK_ENV=${PREVIOUS_EB_ENV}
