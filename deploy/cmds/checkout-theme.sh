#! /bin/bash

###
# Checkout version of theme defined in config.sh
###

set -e
CMDS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $CMDS_DIR/..
source ./config.sh

cd $PARENT_DIR/$THEME_REPO_NAME
git pull
git checkout $THEME_TAG
