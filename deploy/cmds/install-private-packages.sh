#! /bin/bash

###
# NPM install everything we need
###

set -e
CMDS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $CMDS_DIR
source ./config.sh

for package in "${NPM_PRIVATE_PACKAGES[@]}"; do
  (cd $package && $NPM i)
done
