#!/usr/bin/env bash
set -e

cd $(dirname $0)/../../

echo -e "\nPHP Mess Detector\n"
./vendor/bin/phpmd src text ci/qa-config/phpmd.xml --exclude */Tests/*
