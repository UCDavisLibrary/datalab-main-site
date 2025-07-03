# Main Datalab Website

Repository for the main UC Davis Datalab website, running Wordpress with the UC Davis Library theme.

https://www.figma.com/file/chC7MLHfapKSRsTXFWNLRs/DataLab?type=design&node-id=371-502&mode=design

## Deployment

The deployment process follows the same general strategy as the [main library website](https://github.com/UCDavisLibrary/main-wp-website-deployment).

### Build and Tag
1. Create a PR into main, and merge
2. Pull most recent changes on main and tag your release: `git tag v1.2.3` `git push origin --tags`
3. Update build information in [cork-build-registry](https://github.com/ucd-library/cork-build-registry)
4. Build image with `deploy/cmds/build.sh <tag>`
5. Update image version in `deploy/compose/datalab-main-site-prod/compose.yaml` and push changes

### Determine Server
Determine which instance of the site is running with `curl -I https://datalab.ucdavis.edu`. The `ROUTEID` cookie will be either `v2.blue` or `v2.gold`. You will be deploying to the instance not currently running.

### Backup Data
To ensure we have the absolute freshest data, the current instance should be backed up to Google Cloud.
```bash
ssh <old-color>-datalab.library.ucdavis.edu
cd /opt/datalab-main-site/deploy/compose/datalab-main-site-prod
docker compose exec backup /deploy-utils/backup/backup.sh
```

### Set Up New Instance
```bash
ssh <new-color>-datalab.library.ucdavis.edu
cd /opt/datalab-main-site/deploy/compose/datalab-main-site-prod
git pull # ensure prod compose file is up-to-date
docker compose down -v # drop volume from last time this instance was active
docker compose pull # get our newly built image
```

#### Download Data
Now we need to download the data we backed up from the current instance. First, ensure that  `HOST_PORT` in the env file is not on the production port (80). Next, run `docker compose up -d` and follow along with the init script with `docker compose logs init -f`. Once the init script completes, take down the cluster with `docker compose down`

#### Deploy and Verify
Now, we can change `HOST_PORT` to `80` and run `docker compose up -d`. At this point, we will have two instances publicly available at the same time. To verify that the new instance is working properly, delete the `ROUTEID` cookie, and reload the website in your browser. Keep doing this until the cookie is set to the new instance. If everything looks good, you can move onto removing the old instance.

### Removing Old Instance
On the old instance server, run `docker compose down` in `/opt/datalab-main-site/deploy/compose/datalab-main-site-prod`. And for extra caution, change `HOST_PORT` from `80`. 

### Post-Launch Tasks
- Log in as an admin (you might be prompted to update the database)
- Clear the page cache. `Hummingbird Pro -> Caching -> Clear Cache`

## Development

To get the site up and running on your machine:

1. Create a directory, and clone this repository and the [ucdlib wordpress theme](https://github.com/UCDavisLibrary/ucdlib-theme-wp). You will likely want to be on the stage branch.
2. Enter this repo and run `deploy/cmds/init-local-dev.sh`. You will need permissions to download the env and bucket reader GC secrets.
3. Review env file in `deploy/compose/datalab-main-site-local-dev`
4. Build local images with `deploy/cmds/build-local-dev.sh stage`
5. Get cluster up and running with `cd deploy/compose/datalab-main-site-local-dev && docker compose up -d`

To start js watch process, run `npm run watch` in:
- `src/plugins/ucdlib-datalab/assets/editor` for block editor bundle
- `src/plugins/ucdlib-datalab/assets/public` for public bundle

### Updating a Third Party Plugin
All plugins are version-controlled, hosted in a Google Cloud Bucket, and downloaded into the image during the build process. To update a plugin:
1. Download and upload the plugin to the Google Cloud Bucket specified in `GC_BUCKET_PLUGINS` in the `Dockerfile`.
2. Update the plugin version at the top of the `Dockerfile`
