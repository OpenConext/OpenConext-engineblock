#!/bin/sh

REJFILE="/tmp/janus_patches-rej-$(date +%s)"
for FILENAME in janus_patches/*.patch
do
  patch -Np0 -r $REJFILE < $FILENAME
done
rm $REJFILE
echo "Removed $REJFILE"
