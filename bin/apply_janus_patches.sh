#!/bin/sh

for FILENAME in janus_patches/*.patch
do
  patch -Np0 -r /dev/null < $FILENAME
done
