# Main Datalab Website

## Deployment

## Development

To get the app up and running on your machine:

1. `cd deploy`
2. Make sure `GC_READER_KEY_SECRET` has read access to `GC_BUCKET_PLUGINS`, and that you have access to view the secret.
3. `./cmds/init-local-dev.sh`
4. `./cmds/build-local-dev.sh`
5. `./cmds/generate-deployment-files.sh`
6. A directory called `$APP_SLUG-local-dev` will have been created.
7. Enter it, and run `docker compose up`

If you are using the init/backup utilities, you will need make sure that you have access to the service account secrets. `gc-reader-key.json` and `gc-writer-key.json` should have content for the init and backup containers, respectively. Keys are fetced in `init-local-dev`, but they also have their own dedicated scripts.

### Adding a Third Party Plugin
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

