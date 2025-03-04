#! /bin/bash

###
# Main build process to cutting production images
###

VERSION=$1
if [ -z "$VERSION" ]; then
  echo "Please provide a version number"
  exit 1
fi

cork-kube build gcb \
  --project datalab-main-site \
  --version $VERSION \
  --high-cpu \
  --depth ALL
