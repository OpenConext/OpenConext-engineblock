#!/bin/sh

RELEASE_DIR=${HOME}/Releases
GITHUB_USER=OpenConext
PROJECT_NAME=OpenConext-engineblock

if [ -z "$1" ]
then

cat << EOF
Please specify the tag or branch to make a release of.

Examples:
    
    sh makeRelease.sh 0.1.0
    sh makeRelease.sh master
    sh makeRelease.sh develop

If you want to GPG sign the release, you can specify the "sign" parameter, this will
invoke the gpg command line tool to sign it.

   sh makeRelease 0.1.0 sign

EOF
exit 1
else
    TAG=$1
fi

mkdir -p ${RELEASE_DIR}
rm -rf ${RELEASE_DIR}/${PROJECT_NAME}

# get Composer
(
cd ${RELEASE_DIR}
curl -O http://getcomposer.org/composer.phar
)

# clone the tag
(
cd ${RELEASE_DIR}
    git clone -b ${TAG} https://github.com/${GITHUB_USER}/${PROJECT_NAME}.git
)

# run Composer
(
cd ${RELEASE_DIR}/${PROJECT_NAME}
php ${RELEASE_DIR}/composer.phar install --no-dev
)

# remove Git and Composer files
(
rm -rf ${RELEASE_DIR}/${PROJECT_NAME}/.git
rm -f ${RELEASE_DIR}/${PROJECT_NAME}/.gitignore
rm -f ${RELEASE_DIR}/${PROJECT_NAME}/composer.json
rm -f ${RELEASE_DIR}/${PROJECT_NAME}/composer.lock
rm -f ${RELEASE_DIR}/${PROJECT_NAME}/makeRelease.sh
rm -f ${RELEASE_DIR}/${PROJECT_NAME}/bin/composer.phar
rm -rf ${RELEASE_DIR}/${PROJECT_NAME}/features
rm -rf ${RELEASE_DIR}/${PROJECT_NAME}/behat.yml
rm -rf ${RELEASE_DIR}/${PROJECT_NAME}/build.xml
rm -rf ${RELEASE_DIR}/${PROJECT_NAME}/tests
rm -rf ${RELEASE_DIR}/${PROJECT_NAME}/ci

)

# create tarball
(
cd ${RELEASE_DIR}

tar -czf ${PROJECT_NAME}-${TAG}.tar.gz ${PROJECT_NAME}
)

# create checksum file
(
cd ${RELEASE_DIR}
shasum ${PROJECT_NAME}-${TAG}.tar.gz > ${PROJECT_NAME}.sha
)

# sign it if requested
(
if [ -n "$2" ]
then
	if [ "$2" == "sign" ]
	then
		cd ${RELEASE_DIR}
		gpg -o ${PROJECT_NAME}.sha.gpg  --clearsign ${PROJECT_NAME}.sha
	fi
fi
)
