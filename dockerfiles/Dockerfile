# Multistage build args
ARG WP_CORE_VERSION="6.6.2"
ARG REDIRECTION_ZIP_FILE="redirection-5.5.0.zip"
ARG SMTP_MAILER_ZIP_FILE="smtp-mailer-1.1.15.zip"
ARG OPENID_CONNECT_GENERIC_DIR="openid-connect-generic-3.10.0"
ARG OPENID_CONNECT_GENERIC_ZIP_FILE="${OPENID_CONNECT_GENERIC_DIR}.zip"
ARG DEFENDER_PRO_ZIP_FILE="defender-pro-4.10.zip"
ARG FORMINATOR_PRO_ZIP_FILE="forminator-pro-1.35.1.zip"
ARG HUMMINGBIRD_PRO_ZIP_FILE="hummingbird-pro-3.9.4.zip"
ARG SMUSH_PRO_ZIP_FILE="smush-pro-3.16.8.zip"
ARG WPMU_DEV_DASHBOARD_ZIP_FILE="wpmu-dev-dashboard-4.11.26.zip"
ARG BROKEN_LINK_CHECKER_ZIP_FILE="broken-link-checker-2.4.1.zip"
ARG UCDLIB_THEME_WP_NODE_IMAGE

# Download plugins from Google Cloud Storage
FROM google/cloud-sdk:alpine as gcloud
RUN mkdir -p /cache
WORKDIR /cache
ARG GC_BUCKET_PLUGINS="wordpress-general/plugins"
ARG REDIRECTION_ZIP_FILE
ARG OPENID_CONNECT_GENERIC_ZIP_FILE
ARG SMTP_MAILER_ZIP_FILE
ARG DEFENDER_PRO_ZIP_FILE
ARG FORMINATOR_PRO_ZIP_FILE
ARG HUMMINGBIRD_PRO_ZIP_FILE
ARG SMUSH_PRO_ZIP_FILE
ARG WPMU_DEV_DASHBOARD_ZIP_FILE
ARG BROKEN_LINK_CHECKER_ZIP_FILE

RUN echo $GOOGLE_KEY_FILE_CONTENT | gcloud auth activate-service-account --key-file=-
RUN gsutil cp gs://${GC_BUCKET_PLUGINS}/openid-connect-generic/${OPENID_CONNECT_GENERIC_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/smtp-mailer/${SMTP_MAILER_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/redirection/${REDIRECTION_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/defender-pro/${DEFENDER_PRO_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/forminator-pro/${FORMINATOR_PRO_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/hummingbird-pro/${HUMMINGBIRD_PRO_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/smush-pro/${SMUSH_PRO_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/wpmudev-updates/${WPMU_DEV_DASHBOARD_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/broken-link-checker/${BROKEN_LINK_CHECKER_ZIP_FILE} .

# Main build
FROM ${UCDLIB_THEME_WP_NODE_IMAGE} AS ucdlib-theme-wp
FROM wordpress:${WP_CORE_VERSION} as wordpress

# WP Filesystem paths
ARG WP_LOG_ROOT
ARG WP_SRC_ROOT=/usr/src/wordpress
ARG WP_CONTENT_DIR=$WP_SRC_ROOT/wp-content
ARG WP_THEME_DIR=$WP_CONTENT_DIR/themes
ARG WP_PLUGIN_DIR=$WP_CONTENT_DIR/plugins
ARG WP_UPLOADS_DIR=$WP_CONTENT_DIR/uploads

# WP Filesystem env vars
ENV WP_LOG_ROOT=${WP_LOG_ROOT}
ENV WP_SRC_ROOT=${WP_SRC_ROOT}
ENV WP_UPLOADS_DIR=${WP_UPLOADS_DIR}

WORKDIR $WP_SRC_ROOT

# node setup
ARG NODE_VERSION
RUN apt-get update \
&& apt-get install -y ca-certificates curl gnupg \
&& mkdir -p /etc/apt/keyrings \
&& curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
&& echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list

# Install Composer Package Manager (theme needs Timber and Twig)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1;
COPY composer.json .
RUN composer install

# Install debian packages
RUN apt-get update && apt-get install -y unzip git vim nodejs

# WP config
COPY wp-config-docker.php wp-config-docker.php

# directories needed by hummingbird cache plugin
RUN mkdir wp-content/wphb-cache; \
    mkdir wp-content/wphb-logs; \
	chown www-data wp-content/wphb-logs; \
	chgrp www-data wp-content/wphb-logs; \
	chown www-data wp-content/wphb-cache; \
	chgrp www-data wp-content/wphb-cache

# Apache config
COPY .htaccess .htaccess

# Switch apache to use wp src
RUN set -eux; \
	find /etc/apache2 -name '*.conf' -type f -exec sed -ri -e "s!/var/www/html!$PWD!g" -e "s!Directory /var/www/!Directory $PWD!g" '{}' +; \
	cp -s wp-config-docker.php wp-config.php

# WP CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
&& chmod +x wp-cli.phar \
&& mv wp-cli.phar /usr/local/bin/wp

# copy theme
WORKDIR $WP_THEME_DIR
RUN rm -rf */
COPY --from=ucdlib-theme-wp /plugin/ucdlib-theme-wp $WP_THEME_DIR/ucdlib-theme-wp
RUN cd $WP_THEME_DIR/ucdlib-theme-wp/src/editor && npm link \
&& cd $WP_THEME_DIR/ucdlib-theme-wp/src/public && npm link

# remove default plugins and insert the plugins we downloaded from GCS
ARG OPENID_CONNECT_GENERIC_DIR
ARG OPENID_CONNECT_GENERIC_ZIP_FILE
ARG REDIRECTION_ZIP_FILE
ARG SMTP_MAILER_ZIP_FILE
ARG DEFENDER_PRO_ZIP_FILE
ARG FORMINATOR_PRO_ZIP_FILE
ARG HUMMINGBIRD_PRO_ZIP_FILE
ARG SMUSH_PRO_ZIP_FILE
ARG WPMU_DEV_DASHBOARD_ZIP_FILE
ARG BROKEN_LINK_CHECKER_ZIP_FILE
WORKDIR $WP_PLUGIN_DIR
RUN rm -rf */ && rm -f hello.php
COPY --from=gcloud /cache/${OPENID_CONNECT_GENERIC_ZIP_FILE} .
COPY --from=gcloud /cache/${REDIRECTION_ZIP_FILE} .
COPY --from=gcloud /cache/${SMTP_MAILER_ZIP_FILE} .
COPY --from=gcloud /cache/${DEFENDER_PRO_ZIP_FILE} .
COPY --from=gcloud /cache/${FORMINATOR_PRO_ZIP_FILE} .
COPY --from=gcloud /cache/${HUMMINGBIRD_PRO_ZIP_FILE} .
COPY --from=gcloud /cache/${SMUSH_PRO_ZIP_FILE} .
COPY --from=gcloud /cache/${WPMU_DEV_DASHBOARD_ZIP_FILE} .
COPY --from=gcloud /cache/${BROKEN_LINK_CHECKER_ZIP_FILE} .
RUN unzip ${OPENID_CONNECT_GENERIC_ZIP_FILE} && rm ${OPENID_CONNECT_GENERIC_ZIP_FILE} \
&& unzip ${SMTP_MAILER_ZIP_FILE} && rm ${SMTP_MAILER_ZIP_FILE} \
&& unzip ${REDIRECTION_ZIP_FILE} && rm ${REDIRECTION_ZIP_FILE} \
&& unzip ${DEFENDER_PRO_ZIP_FILE} && rm ${DEFENDER_PRO_ZIP_FILE} \
&& unzip ${FORMINATOR_PRO_ZIP_FILE} && rm ${FORMINATOR_PRO_ZIP_FILE} \
&& unzip ${HUMMINGBIRD_PRO_ZIP_FILE} && rm ${HUMMINGBIRD_PRO_ZIP_FILE} \
&& unzip ${SMUSH_PRO_ZIP_FILE} && rm ${SMUSH_PRO_ZIP_FILE} \
&& unzip ${WPMU_DEV_DASHBOARD_ZIP_FILE} && rm ${WPMU_DEV_DASHBOARD_ZIP_FILE} \
&& unzip ${BROKEN_LINK_CHECKER_ZIP_FILE} && rm ${BROKEN_LINK_CHECKER_ZIP_FILE} \
&& mv $OPENID_CONNECT_GENERIC_DIR openid-connect-generic

# Get plugins from github
RUN git -c advice.detachedHead=false \
	clone https://github.com/UCDavisLibrary/forminator-theme-styles.git \
	--branch v1.1.0 --single-branch --depth 1

# Copy our custom plugins
COPY src/plugins/ucdlib-oidc ucdlib-oidc
COPY src/plugins/ucdlib-datalab ucdlib-datalab

# asset build
RUN cd ucdlib-datalab/assets/public \
&& npm install \
&& npm link @ucd-lib/brand-theme \
&& npm run dist \
&& cd ../editor \
&& npm install \
&& npm link @ucd-lib/brand-theme-editor \
&& npm run dist \
&& rm -rf node_modules \
&& rm -rf ../public/node_modules \
&& rm -rf ${THEME_REPO_NAME}/src/public/node_modules \
&& rm -rf ${THEME_REPO_NAME}/src/editor/node_modules

# Back to site root so wordpress can do the rest of its thing
WORKDIR $WP_SRC_ROOT

# override docker entry point, so we can ensure apache can write to our volumes
COPY docker-entrypoint.sh /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

RUN mkdir /cork-build-info
COPY --from=ucdlib-theme-wp /cork-build-info/ucdlib-theme-wp.json /cork-build-info/ucdlib-theme-wp.json

# start apache
CMD ["apache2-foreground"]
