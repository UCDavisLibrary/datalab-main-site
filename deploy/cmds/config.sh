#! /bin/bash
CMDS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
PROJECT_DIR=$CMDS_DIR/../../..
REPO_ROOT=$CMDS_DIR/../..
THEME_REPO_NAME=ucdlib-theme-wp

# NPM
NPM=npm
NPM_PRIVATE_PACKAGES=(
  $PROJECT_DIR/$THEME_REPO_NAME/src/public
  $PROJECT_DIR/$THEME_REPO_NAME/src/editor
  $REPO_ROOT/src/plugins/ucdlib-datalab/assets/public
  $REPO_ROOT/src/plugins/ucdlib-datalab/assets/editor
)
JS_BUNDLES=(
  $REPO_ROOT/src/plugins/ucdlib-datalab/assets/public
  $REPO_ROOT/src/plugins/ucdlib-datalab/assets/editor
)
