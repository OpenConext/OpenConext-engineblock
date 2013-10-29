#!/bin/sh
# @todo add more error handling

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

PROJECT_DIR_NAME=${PROJECT_NAME}-${TAG}
PROJECT_DIR=${RELEASE_DIR}/${PROJECT_DIR_NAME}

# Create empty dir
mkdir -p ${RELEASE_DIR}
rm -rf ${PROJECT_DIR}

# get Composer
cd ${RELEASE_DIR}
curl -O http://getcomposer.org/composer.phar

# clone the tag
cd ${RELEASE_DIR}
git clone -b ${TAG} https://github.com/${GITHUB_USER}/${PROJECT_NAME}.git ${PROJECT_DIR_NAME}

# run Composer
cd ${PROJECT_DIR}
php ${RELEASE_DIR}/composer.phar install --no-dev

# run Assetic
cd ${PROJECT_DIR}/bin
rm -fr ${PROJECT_DIR}/www/authentication/generated
php ./assets_pipelines.php

# remove files that are not required for production
rm -rf ${PROJECT_DIR}/.idea
rm -rf ${PROJECT_DIR}/.git
rm -f ${PROJECT_DIR}/.gitignore
rm -f ${PROJECT_DIR}/composer.json
rm -f ${PROJECT_DIR}/composer.lock
rm -f ${PROJECT_DIR}/makeRelease.sh
rm -f ${PROJECT_DIR}/bin/composer.phar
rm -rf ${PROJECT_DIR}/features
rm -rf ${PROJECT_DIR}/behat.yml
rm -rf ${PROJECT_DIR}/build.xml
rm -rf ${PROJECT_DIR}/tests
rm -rf ${PROJECT_DIR}/ci
rm -rf ${PROJECT_DIR}/.travis.yml

# create tarball
cd ${RELEASE_DIR}
tar -czf ${PROJECT_DIR_NAME}.tar.gz ${PROJECT_DIR_NAME}


# create checksum file
cd ${RELEASE_DIR}
# sha1sum ${PROJECT_DIR_NAME}.tar.gz > ${PROJECT_DIR_NAME}.sha

# sign it if requested
if [ -n "$2" ]
then
	if [ "$2" == "sign" ]
	then
		cd ${RELEASE_DIR}
		gpg -o ${PROJECT_DIR_NAME}.sha.gpg  --clearsign ${PROJECT_DIR_NAME}.sha
	fi
fi