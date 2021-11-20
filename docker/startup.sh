# Please do not manually call this file!
# This script is run by the docker container when it is "run"


# Create the .env file
php /root/create-env-file.php /.env


# Run the apache process in the background
source /etc/apache2/envvars
/usr/sbin/service apache2 start


# Start the cron service in the foreground
# We dont run apache in the FG, so that we can restart apache without container
# exiting.
cron -f