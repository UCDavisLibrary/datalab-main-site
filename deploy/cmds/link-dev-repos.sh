#! /bin/bash

###
# For local development, make symlinks to the repos that are dependencies of this site.
# These should all be in the same parent directory as this repo.
###

set -e
CMDS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $CMDS_DIR/..
source config.sh

if [ -d "${REPOSITORY_DIR}" ]; then
  rm -rf $REPOSITORY_DIR
fi
mkdir $REPOSITORY_DIR

for repo in "${DEV_REPOS[@]}"; do
  ln -s $PARENT_DIR/$repo $REPOSITORY_DIR/$repo
done
