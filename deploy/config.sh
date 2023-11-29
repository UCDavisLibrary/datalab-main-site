#! /bin/bash

######### DEPLOYMENT CONFIG ############
# Setup your application deployment here
########################################

# Grab build number is mounted in CI system
if [[ -f /config/.buildenv ]]; then
  source /config/.buildenv
else
  BUILD_NUM=-1
fi

# Used to name the container image among other things
APP_SLUG=datalab-main-site

# Main version number we are tagging the app with. Always update
# this when you cut a new version of the app!
APP_VERSION=v0.0.9.${BUILD_NUM}

# Repository tags/branchs
# Tags should always be used for production deployments
# Branches can be used for development deployments
REPO_TAG=sandbox

# Dependency tags/branches
THEME_TAG='v3.7.0'
WP_CORE_VERSION='6.4.1'
MYSQL_TAG=5.7
ADMINER_TAG=4
NODE_VERSION=20

# Plugin versions from Google Cloud Storage
OPENID_CONNECT_GENERIC_VERSION='3.9.1'
REDIRECTION_VERSION='5.3.10'
SMTP_MAILER_VERSION='1.1.9'
DEFENDER_PRO_VERSION='4.2.1'
FORMINATOR_PRO_VERSION='1.27'
HUMMINGBIRD_PRO_VERSION='3.6'
SMUSH_PRO_VERSION='3.15'
WPMU_DEV_DASHBOARD_VERSION='4.11.22'

# Plugin versions from github
FORMS_STYLES_VERSION='v1.1.0'

# Auth Defaults ( can also be overriden in .env file )
OIDC_PROVIDER_URL='https://sandbox.auth.library.ucdavis.edu/realms/internal'
# OIDC_PROVIDER_URL='https://auth.library.ucdavis.edu/realms/internal'
OIDC_CLIENT_ID=$APP_SLUG
#OIDC_CLIENT_SECRET='set this in your .env file'
OIDC_PROTOCOL_URL=$OIDC_PROVIDER_URL/protocol/openid-connect
OIDC_ENDPOINT_LOGIN_URL=$OIDC_PROTOCOL_URL/auth
OIDC_ENDPOINT_USERINFO_URL="" # if left blank, will use id token for verifying user
#OIDC_ENDPOINT_USERINFO_URL=$OIDC_PROTOCOL_URL/userinfo
OIDC_ENDPOINT_TOKEN_URL=$OIDC_PROTOCOL_URL/token
OIDC_ENDPOINT_LOGOUT_URL=$OIDC_PROTOCOL_URL/logout
OIDC_CLIENT_SCOPE='openid profile email roles'
OIDC_LOGIN_TYPE='auto'
OIDC_CREATE_IF_DOES_NOT_EXIST='true'
OIDC_LINK_EXISTING_USERS='true'
OIDC_REDIRECT_USER_BACK='true'
OIDC_ENFORCE_PRIVACY='false' # if true, will protect all pages with login

##
# Repositories
##

GITHUB_ORG_URL=https://github.com/UCDavisLibrary

# theme
THEME_REPO_NAME=ucdlib-theme-wp
THEME_REPO_URL=$GITHUB_ORG_URL/$THEME_REPO_NAME

# form styles plugin
FORMS_STYLES_REPO_NAME=forminator-theme-styles
FORMS_STYLES_REPO_URL=$GITHUB_ORG_URL/$FORMS_STYLES_REPO_NAME

# local development repository dependencies
DEV_REPOS=(
  $THEME_REPO_NAME
)

##
# Container
##

# Container Registery
# We use the private container registry since some of our plugins are paid
CONTAINER_REG_ORG=gcr.io/digital-ucdavis-edu

if [[ -z $BRANCH_NAME ]]; then
 CONTAINER_CACHE_TAG=$(git rev-parse --abbrev-ref HEAD)
else
 CONTAINER_CACHE_TAG=$BRANCH_NAME
fi

# set localhost/local-dev used by
# local development docker-compose file
if [[ ! -z $LOCAL_BUILD ]]; then
  CONTAINER_REG_ORG='localhost/local-dev'
fi

# This will be name of directory that contains local development docker compose file and env
LOCAL_DEV_DIRECTORY=$APP_SLUG-local-dev

# Container Images
APP_IMAGE_NAME=$CONTAINER_REG_ORG/$APP_SLUG
APP_UTILS_IMAGE_NAME=$APP_IMAGE_NAME-utils
MYSQL_IMAGE_NAME=mysql
ADMINER_IMAGE_NAME=adminer

APP_IMAGE_NAME_TAG=$APP_IMAGE_NAME:$REPO_TAG
APP_UTILS_IMAGE_NAME_TAG=$APP_UTILS_IMAGE_NAME:$REPO_TAG
MYSQL_IMAGE_NAME_TAG=$MYSQL_IMAGE_NAME:$MYSQL_TAG
ADMINER_IMAGE_NAME_TAG=$ADMINER_IMAGE_NAME:$ADMINER_TAG

ALL_DOCKER_BUILD_IMAGES=( $APP_IMAGE_NAME $APP_UTILS_IMAGE_NAME )

ALL_DOCKER_BUILD_IMAGE_TAGS=(
  $APP_IMAGE_NAME_TAG
  $APP_UTILS_IMAGE_NAME_TAG
)

# Project Directories
DEPLOY_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
CMDS_DIR=$DEPLOY_DIR/cmds
UTILS_DIR=$DEPLOY_DIR/utils
ROOT_DIR="$( cd $DEPLOY_DIR/.. && pwd )"
PARENT_DIR="$( cd $DEPLOY_DIR/../.. && pwd )"
SRC_DIR=$ROOT_DIR/src
REPOSITORY_DIR=$ROOT_DIR/repositories

# WP Directories (in container)
WP_SRC_ROOT=/usr/src/wordpress
WP_LOG_ROOT=/var/log/wordpress
WP_CONTENT_DIR=$WP_SRC_ROOT/wp-content
WP_THEME_DIR=$WP_CONTENT_DIR/themes
WP_UCD_THEME_DIR=$WP_THEME_DIR/$THEME_REPO_NAME
WP_PLUGIN_DIR=$WP_CONTENT_DIR/plugins
WP_UPLOADS_DIR=$WP_CONTENT_DIR/uploads

# NPM
NPM=npm
NPM_PRIVATE_PACKAGES=(
  $REPOSITORY_DIR/$THEME_REPO_NAME/src/public
  $SRC_DIR/plugins/ucdlib-datalab/assets/public
)
JS_BUNDLES=(
  $SRC_DIR/plugins/ucdlib-datalab/assets/public
)

# Google Cloud
# Used for init/backup scripts and for installing plugins in Docker image
# You will need to get a service account key(s) with ./cmds/get-reader-key.sh or ./cmds/get-writer-key.sh
# The reader should have access to both GC_BUCKET_PLUGINS and GC_BUCKET_BACKUPS
GC_PROJECT=digital-ucdavis-edu
GC_READER_KEY_SECRET=$APP_SLUG-reader
GC_WRITER_KEY_SECRET=$APP_SLUG-writer
GC_BUCKET_PLUGINS=wordpress-general/plugins
GC_BUCKET_BACKUPS=$APP_SLUG-content
BACKUP_FILE_NAME="db.sql.gz"
UPLOADS_FILE_NAME="uploads.tar.gz"
# You may also need to set additional variables in your env file (if not pre-set in docker-compose file):
# RUN_INIT/INIT_DATA_ENV - used to hydrate db on startup
# RUN_BACKUP/BACKUP_DATA_ENV - used to backup db nightly
