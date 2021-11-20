# Due to layout of this project, the dockerfile will be moved up two directories and run during
# the build. Thus when performing any ADD commands, remember that this is "where you are"

FROM ubuntu:20.04

# Don't prompt for timezone etc.
env DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get dist-upgrade -y

RUN apt-get install -y software-properties-common apt-transport-https \
  && add-apt-repository ppa:ondrej/php -y \
  && apt-get update \
  && apt-get install -y php8.0-cli cron vim apache2 apache2-mod-php8.0 curl php8.0-curl php8.0-dom php8.0-mbstring

# Enable the php mod we just installed
RUN a2enmod php8.0
RUN a2enmod rewrite


# expose port 80 and 443 (ssl) for the web requests
EXPOSE 80
EXPOSE 443

# Manually set the apache environment variables in order to get apache to work immediately.
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# It appears that the new apache requires these env vars as well
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid


# Set display errors to true. We will disable them based on environment in PHP.
RUN sed -i 's;display_errors = .*;display_errors = On;' /etc/php/8.0/apache2/php.ini
RUN sed -i 's;display_errors = .*;display_errors = On;' /etc/php/8.0/cli/php.ini


# Add the startup script to the container. This will run when container starts.
ADD docker/startup.sh /root/startup.sh

# Update our apache sites available with the config we created
ADD docker/apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# Use the crontab file.
# The crontab file was already added when we added "project"
ADD docker/crons.conf /root/crons.conf
RUN crontab /root/crons.conf

# Add our websites files to the default apache directory (/var/www)
# This should be as late as possible in the dockerfile to speed up rebuilds
ADD site /var/www/site

# Add the script for creating an env file to the container for execution on startup.
ADD docker/create-env-file.php /root/create-env-file.php

# Execute the containers startup script which will start many processes/services
# The startup file was already added when we added "project"
CMD ["/bin/bash", "/root/startup.sh"]
