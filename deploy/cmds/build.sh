#! /bin/bash

###
# Main build process to cutting production images
###

set -e
CMDS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $CMDS_DIR/..
source config.sh

# Use buildkit to speedup local builds
# Not supported in google cloud build yet
if [[ -z $CLOUD_BUILD ]]; then
  export DOCKER_BUILDKIT=1
fi

# Application
docker build \
  -t $APP_IMAGE_NAME_TAG \
  --cache-from=$APP_IMAGE_NAME:$CONTAINER_CACHE_TAG \
  --build-arg GITHUB_ORG_URL=${GITHUB_ORG_URL} \
  --build-arg GC_BUCKET_PLUGINS=${GC_BUCKET_PLUGINS} \
  --build-arg OPENID_CONNECT_GENERIC_VERSION=${OPENID_CONNECT_GENERIC_VERSION} \
  --build-arg REDIRECTION_VERSION=${REDIRECTION_VERSION} \
  --build-arg DEFENDER_PRO_VERSION=${DEFENDER_PRO_VERSION} \
  --build-arg FORMINATOR_PRO_VERSION=${FORMINATOR_PRO_VERSION} \
  --build-arg HUMMINGBIRD_PRO_VERSION=${HUMMINGBIRD_PRO_VERSION} \
  --build-arg SMUSH_PRO_VERSION=${SMUSH_PRO_VERSION} \
  --build-arg WPMU_DEV_DASHBOARD_VERSION=${WPMU_DEV_DASHBOARD_VERSION} \
  --build-arg SMTP_MAILER_VERSION=${SMTP_MAILER_VERSION} \
  --build-arg BROKEN_LINK_CHECKER_VERSION=${BROKEN_LINK_CHECKER_VERSION} \
  --build-arg FORMS_STYLES_VERSION=${FORMS_STYLES_VERSION} \
  --build-arg FORMS_STYLES_REPO_NAME=${FORMS_STYLES_REPO_NAME} \
  --build-arg THEME_TAG=${THEME_TAG} \
  --build-arg THEME_REPO_NAME=${THEME_REPO_NAME} \
  --build-arg WP_CORE_VERSION=${WP_CORE_VERSION} \
  --build-arg WP_SRC_ROOT=${WP_SRC_ROOT} \
  --build-arg WP_LOG_ROOT=${WP_LOG_ROOT} \
  --build-arg WP_UPLOADS_DIR=${WP_UPLOADS_DIR} \
  --build-arg WP_THEME_DIR=${WP_THEME_DIR} \
  --build-arg WP_PLUGIN_DIR=${WP_PLUGIN_DIR} \
  --build-arg BUILDKIT_INLINE_CACHE=1 \
  --build-arg NODE_VERSION=${NODE_VERSION} \
  --build-arg BUILD_NUM=${BUILD_NUM} \
  --build-arg BUILD_TIME=${BUILD_TIME} \
  --build-arg APP_VERSION=${APP_VERSION} \
  $ROOT_DIR

# Deploy utils
docker build \
  -t $APP_UTILS_IMAGE_NAME_TAG \
  --cache-from=$APP_UTILS_IMAGE_NAME:$CONTAINER_CACHE_TAG \
  --build-arg APP_IMAGE_NAME_TAG=${APP_IMAGE_NAME_TAG} \
  $UTILS_DIR
