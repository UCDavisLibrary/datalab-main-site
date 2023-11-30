#! /bin/bash

###
# download the env file from the secret manager
# first arg is either 'prod' or 'dev'
# You will want to review the env file before using it, since it has values for both dev and prod
###

set -e
CMDS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $CMDS_DIR/..
source config.sh

ENV_TYPE="${1:-prod}"
if [ "$ENV_TYPE" != "prod" ]; then
  ENV_TYPE="dev"
  ENV_FILE="./${LOCAL_DEV_DIRECTORY}/.env"
else
  ENV_TYPE="prod"
  ENV_FILE="./.env"
fi

if [ -e "$ENV_FILE" ]; then
  echo "Error: $ENV_FILE already exists. Please remove it and try again."
  exit 1
fi

echo "Downloading env file and placing it in ${ENV_FILE} of deployment directory"

gcloud secrets versions access latest --secret="${APP_SLUG}-env" > $ENV_FILE

echo "Remember to review the env file before using it, since it has values for both dev and prod"
