#! /bin/bash

###
# download the google cloud writer key from the secret manager
###

set -e
CMDS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $CMDS_DIR/..

mkdir -p ./secrets
gcloud --project=digital-ucdavis-edu secrets versions access latest --secret=datalab-main-site-writer > ./secrets/gc-writer-key.json
