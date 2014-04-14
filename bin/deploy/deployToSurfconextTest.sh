#!/bin/sh

if [ -z "$1" ]; then
    cat << EOF
Please specify the tag or branch to make a release of.

Examples:
    
    sh bin/deploy/deployToSurfconextTest.sh 0.1.0
    sh bin/deploy/deployToSurfconextTest.sh master
    sh bin/deploy/deployToSurfconextTest.sh develop
EOF
    exit 1
else TAG=$1; fi
if [ -z "$2" ]; then DEPLOY_ADDRESS="lucas@surf-test"; else DEPLOY_ADDRESS="$2"; fi

if [ ! -f ~/Releases/OpenConext-engineblock-${TAG}.tar.gz ]; then
    echo "Building a new release" &&
    ./bin/makeRelease.sh ${TAG}
else
    echo "Re-using existing build"
fi

echo "Copy release to test server" &&
scp ~/Releases/OpenConext-engineblock-${TAG}.tar.gz $DEPLOY_ADDRESS:/opt/data/test/ &&

echo "Doing deploy on remote server" &&
ssh $DEPLOY_ADDRESS <<COMMANDS
    cd /opt/data/test &&
    echo "Unpacking files" &&
    tar -xzf OpenConext-engineblock-${TAG}.tar.gz &&
    echo "Removing buildfile" &&
    rm OpenConext-engineblock-${TAG}.tar.gz &&
    echo "Moving release into place" &&
    mv OpenConext-engineblock OpenConext-engineblock-old &&
    mv OpenConext-engineblock-${TAG} OpenConext-engineblock &&
    echo "Running post-install scripts" &&
    cd /opt/www/engineblock &&
    ./bin/migrate &&
    echo "Post install cleanup" &&
    rm -rf OpenConext-engineblock-old
COMMANDS