#!/usr/bin/env sh

cd ../OpenConext-deploy && vagrant ssh -c "cd /opt/openconext/OpenConext-engineblock/ && $*" && cd ../OpenConext-engineblock
