# Main Datalab Website

Repository for the main UC Davis Datalab website, running Wordpress with the UC Davis Library theme.

https://www.figma.com/file/chC7MLHfapKSRsTXFWNLRs/DataLab?type=design&node-id=371-502&mode=design

## Deployment

The deployment process follows the same general strategy as the [main library website](https://github.com/UCDavisLibrary/main-wp-website-deployment).

## Build and Tag
1. Create a PR into main, and merge
2. Pull most recent changes on main and tag your release: `git tag v1.2.3` `git push origin --tags`
3. Update build information in [cork-build-registry](https://github.com/ucd-library/cork-build-registry)
4. Build image with `deploy/cmds/build.sh <tag>`
5. Update image version in `deploy/compose/datalab-main-site-prod/compose.yaml` and push changes

## Determine Server
Determine which instance of the site is running with `curl -I https://datalab.ucdavis.edu`. The `ROUTEID` cookie will be either `v2.blue` or `v2.gold`. You will be deploying to the instance not currently running.

## Backup Data
To ensure we have the absolute freshest data, the current instance should be backed up to Google Cloud.
```bash
ssh <old-color>-datalab.library.ucdavis.edu
cd /opt/datalab-main-site/deploy/compose/datalab-main-site-prod
docker compose exec backup /deploy-utils/backup/backup.sh
```

## Set Up New Instance
```bash
ssh <new-color>-datalab.library.ucdavis.edu
cd /opt/datalab-main-site/deploy/compose/datalab-main-site-prod
git pull # ensure prod compose file is up-to-date
docker compose down -v # drop volume from last time this instance was active
docker compose pull # get our newly built image
```

### Download Data
Now we need to download the data we backed up from the current instance. First, ensure that  `HOST_PORT` in the env file is not on the production port (80). Next, run `docker compose up -d` and follow along with the init script with `docker compose logs init -f`. Once the init script completes, take down the cluster with `docker compose down`

### Deploy and Verify
Now, we can change `HOST_PORT` to `80` and run `docker compose up -d`. At this point, we will have two instances publicly available at the same time. To verify that the new instance is working properly, delete the `ROUTEID` cookie, and reload the website in your browser. Keep doing this until the cookie is set to the new instance. If everything looks good, you can move onto removing the old instance.

## Removing Old Instance
On the old instance server, run `docker compose down` in `/opt/datalab-main-site/deploy/compose/datalab-main-site-prod`. And for extra caution, change `HOST_PORT` from `80`. 

## Post-Launch Tasks
- Log in as an admin (you might be prompted to update the database)
- Clear the page cache. `Hummingbird Pro -> Caching -> Clear Cache`

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

