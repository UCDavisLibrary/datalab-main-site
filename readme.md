# Main Datalab Website

Repository for the main UC Davis Datalab website, running Wordpress with the UC Davis Library theme.

https://www.figma.com/file/chC7MLHfapKSRsTXFWNLRs/DataLab?type=design&node-id=371-502&mode=design

## Deployment

The deployment process follows the same general strategy as the [main library website](https://github.com/UCDavisLibrary/main-wp-website-deployment).

## Development

To get the site up and running on your machine:

1. `cd deploy`
2. Make sure you have access to view the Google Cloud secret defined as `GC_READER_KEY_SECRET`. This ensures that you can download necessary third-party plugins and site content.
3. In the parent directory of this repository, clone all repositories listed in `DEV_REPOS`. These are only needed while doing local development.
4. `./cmds/init-local-dev.sh`
5. `./cmds/build-local-dev.sh`
6. `./cmds/generate-deployment-files.sh`
7. `./cmds/get-env-file.sh dev` to download the env file.
8. You should have a directory called `datalab-main-site-local-dev`.Enter it, and run `docker compose up -d`

If you are using the init/backup utilities, you will need make sure that you have access to the service account secrets. `gc-reader-key.json` and `gc-writer-key.json` should have content for the init and backup containers, respectively. Keys are fetced in `init-local-dev`, but they also have their own dedicated scripts.

### Adding a New Third Party Plugin
All plugins are version-controlled, hosted in a Google Cloud Bucket, and downloaded into the image during the build process. To add a plugin:
1. Download and upload the plugin to the Google Cloud Bucket specified in `GC_BUCKET_PLUGINS`.
2. Add the version to `config.sh`
3. Add as an environmental variable to `build.sh`
4. Define zip filename in `Dockerfile`
5. Define args in `gcloud` and `wordpress` builds in Dockerfile
6. Add to `gsutil cp` command in `gcloud` build
7. Copy, unzip, and rm zip in `wordpress` build
8. Rebuild the image
9. You will still need to go to the Plugins admin page and hit "Activate" or use the wp-cli.

