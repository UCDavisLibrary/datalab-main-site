# Multistage build args
ARG WP_CORE_VERSION
ARG REDIRECTION_VERSION
ARG REDIRECTION_ZIP_FILE="redirection-${REDIRECTION_VERSION}.zip"
ARG SMTP_MAILER_VERSION
ARG SMTP_MAILER_ZIP_FILE="smtp-mailer-${SMTP_MAILER_VERSION}.zip"
ARG OPENID_CONNECT_GENERIC_VERSION
ARG OPENID_CONNECT_GENERIC_DIR="openid-connect-generic-${OPENID_CONNECT_GENERIC_VERSION}"
ARG OPENID_CONNECT_GENERIC_ZIP_FILE="${OPENID_CONNECT_GENERIC_DIR}.zip"
ARG DEFENDER_PRO_VERSION
ARG DEFENDER_PRO_ZIP_FILE="defender-pro-${DEFENDER_PRO_VERSION}.zip"
ARG FORMINATOR_PRO_VERSION
ARG FORMINATOR_PRO_ZIP_FILE="forminator-pro-${FORMINATOR_PRO_VERSION}.zip"
ARG HUMMINGBIRD_PRO_VERSION
ARG HUMMINGBIRD_PRO_ZIP_FILE="hummingbird-pro-${HUMMINGBIRD_PRO_VERSION}.zip"
ARG SMUSH_PRO_VERSION
ARG SMUSH_PRO_ZIP_FILE="smush-pro-${SMUSH_PRO_VERSION}.zip"
ARG WPMU_DEV_DASHBOARD_VERSION
ARG WPMU_DEV_DASHBOARD_ZIP_FILE="wpmu-dev-dashboard-${WPMU_DEV_DASHBOARD_VERSION}.zip"


# Download plugins from Google Cloud Storage
FROM google/cloud-sdk:alpine as gcloud
RUN mkdir -p /cache
WORKDIR /cache
ARG GC_BUCKET_PLUGINS
ARG REDIRECTION_ZIP_FILE
ARG OPENID_CONNECT_GENERIC_ZIP_FILE
ARG SMTP_MAILER_ZIP_FILE
ARG DEFENDER_PRO_ZIP_FILE
ARG FORMINATOR_PRO_ZIP_FILE
ARG HUMMINGBIRD_PRO_ZIP_FILE
ARG SMUSH_PRO_ZIP_FILE
ARG WPMU_DEV_DASHBOARD_ZIP_FILE

COPY deploy/gc-reader-key.json gc-reader-key.json
RUN gcloud auth activate-service-account --key-file=./gc-reader-key.json \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/openid-connect-generic/${OPENID_CONNECT_GENERIC_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/smtp-mailer/${SMTP_MAILER_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/redirection/${REDIRECTION_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/defender-pro/${DEFENDER_PRO_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/forminator-pro/${FORMINATOR_PRO_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/hummingbird-pro/${HUMMINGBIRD_PRO_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/smush-pro/${SMUSH_PRO_ZIP_FILE} . \
&& gsutil cp gs://${GC_BUCKET_PLUGINS}/wpmudev-updates/${WPMU_DEV_DASHBOARD_ZIP_FILE} . \
&& rm gc-reader-key.json

# Main build
FROM wordpress:${WP_CORE_VERSION} as wordpress

# ARGS
ARG APP_VERSION
ENV APP_VERSION ${APP_VERSION}
ARG BUILD_NUM
ENV BUILD_NUM ${BUILD_NUM}
ARG BUILD_TIME
ENV BUILD_TIME ${BUILD_TIME}
ARG WP_SRC_ROOT
ENV WP_SRC_ROOT=${WP_SRC_ROOT}
ARG WP_LOG_ROOT
ENV WP_LOG_ROOT=${WP_LOG_ROOT}
ARG WP_UPLOADS_DIR
ENV WP_UPLOADS_DIR=${WP_UPLOADS_DIR}
ARG WP_THEME_DIR
ARG WP_PLUGIN_DIR
ARG GITHUB_ORG_URL

WORKDIR $WP_SRC_ROOT

# Install Composer Package Manager (theme needs Timber and Twig)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1;
COPY composer.json .
RUN composer install

# node setup
ARG NODE_VERSION
RUN apt-get update \
&& apt-get install -y ca-certificates curl gnupg \
&& mkdir -p /etc/apt/keyrings \
&& curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
&& echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list

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

# WP CLI - a nice thing to have
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
&& chmod +x wp-cli.phar \
&& mv wp-cli.phar /usr/local/bin/wp

# get our prebuilt theme
ARG THEME_TAG
ARG THEME_REPO_NAME
WORKDIR $WP_THEME_DIR
RUN rm -rf */
RUN git clone -b ${THEME_TAG} ${GITHUB_ORG_URL}/${THEME_REPO_NAME} \
&& cd ${THEME_REPO_NAME}/src/public \
&& npm install --only=prod && npm link \
&& cd $WP_THEME_DIR/${THEME_REPO_NAME}/src/editor && npm install --only=prod && npm link

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
WORKDIR $WP_PLUGIN_DIR
RUN rm -rf */ && rm -f hello.php
COPY src/plugins .
COPY --from=gcloud /cache/${OPENID_CONNECT_GENERIC_ZIP_FILE} .
COPY --from=gcloud /cache/${REDIRECTION_ZIP_FILE} .
COPY --from=gcloud /cache/${SMTP_MAILER_ZIP_FILE} .
COPY --from=gcloud /cache/${DEFENDER_PRO_ZIP_FILE} .
COPY --from=gcloud /cache/${FORMINATOR_PRO_ZIP_FILE} .
COPY --from=gcloud /cache/${HUMMINGBIRD_PRO_ZIP_FILE} .
COPY --from=gcloud /cache/${SMUSH_PRO_ZIP_FILE} .
COPY --from=gcloud /cache/${WPMU_DEV_DASHBOARD_ZIP_FILE} .
RUN unzip ${OPENID_CONNECT_GENERIC_ZIP_FILE} && rm ${OPENID_CONNECT_GENERIC_ZIP_FILE} \
&& unzip ${SMTP_MAILER_ZIP_FILE} && rm ${SMTP_MAILER_ZIP_FILE} \
&& unzip ${REDIRECTION_ZIP_FILE} && rm ${REDIRECTION_ZIP_FILE} \
&& unzip ${DEFENDER_PRO_ZIP_FILE} && rm ${DEFENDER_PRO_ZIP_FILE} \
&& unzip ${FORMINATOR_PRO_ZIP_FILE} && rm ${FORMINATOR_PRO_ZIP_FILE} \
&& unzip ${HUMMINGBIRD_PRO_ZIP_FILE} && rm ${HUMMINGBIRD_PRO_ZIP_FILE} \
&& unzip ${SMUSH_PRO_ZIP_FILE} && rm ${SMUSH_PRO_ZIP_FILE} \
&& unzip ${WPMU_DEV_DASHBOARD_ZIP_FILE} && rm ${WPMU_DEV_DASHBOARD_ZIP_FILE} \
&& mv $OPENID_CONNECT_GENERIC_DIR openid-connect-generic

# Get plugins from github
ARG FORMS_STYLES_REPO_NAME
ARG FORMS_STYLES_VERSION
RUN git clone -b ${FORMS_STYLES_VERSION} ${GITHUB_ORG_URL}/${FORMS_STYLES_REPO_NAME}

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

# start apache
CMD ["apache2-foreground"]
