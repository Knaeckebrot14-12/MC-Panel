#!/bin/ash -e
cd /app

mkdir -p /var/log/panel/logs/ /var/log/supervisord/ /var/log/nginx/ /var/log/php7/ \
  && chmod 777 /var/log/panel/logs/ \
  && ln -s /app/storage/logs/ /var/log/panel/

## check for .env file and generate app keys if missing
if [ -f /app/var/.env ]; then
  echo "external vars exist."
  rm -rf /app/.env
  ln -s /app/var/.env /app/
else
  echo "external vars don't exist."
  rm -rf /app/.env
  touch /app/var/.env

  ## manually generate a key because key generate --force fails
  if [ -z $APP_KEY ]; then
     echo -e "Generating key."
     APP_KEY=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
     echo -e "Generated app key: $APP_KEY"
     echo -e "APP_KEY=$APP_KEY" > /app/var/.env
  else
    echo -e "APP_KEY exists in environment, using that."
    echo -e "APP_KEY=$APP_KEY" > /app/var/.env
  fi

  ## generate a random salt for hashids if not provided
  if [ -z $HASHIDS_SALT ]; then
     echo -e "Generating hashids salt."
     HASHIDS_SALT=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9!@#$%^&*()_+?><~' | fold -w 20 | head -n 1)
     echo -e "Generated hashids salt: $HASHIDS_SALT"
     echo -e "HASHIDS_SALT=$HASHIDS_SALT" >> /app/var/.env
  else
    echo -e "HASHIDS_SALT exists in environment, using that."
    echo -e "HASHIDS_SALT=$HASHIDS_SALT" >> /app/var/.env
  fi

  ln -s /app/var/.env /app/
fi

echo "Checking if https is required."
if [ -f /etc/nginx/http.d/panel.conf ]; then
  echo "Using nginx config already in place."
  ## older configs redirect everything on port 80, including Let's Encrypt's renewal check
  if grep -q 'return 301 https' /etc/nginx/http.d/panel.conf && ! grep -q 'acme-challenge' /etc/nginx/http.d/panel.conf; then
    echo "Adding the Let's Encrypt renewal path to the nginx config."
    php -r '$f = "/etc/nginx/http.d/panel.conf"; $c = file_get_contents($f); $c = preg_replace("/^(\s*)return 301 (https:\/\/[^;]+);/m", "\$1location ^~ /.well-known/acme-challenge/ { root /var/www/acme; }\n\$1location / { return 301 \$2; }", $c, 1); file_put_contents($f, $c);'
  fi
  ## configs written by older versions: current TLS only, and the shared security headers
  php /app/.github/docker/nginx-migrate.php /etc/nginx/http.d/panel.conf
  if [ $LE_EMAIL ]; then
    echo "Checking for cert update"
    ## a failed check (e.g. Let's Encrypt briefly unreachable) must not keep the panel from starting
    certbot certonly -d $(echo $APP_URL | sed 's~http[s]*://~~g')  --standalone -m $LE_EMAIL --agree-tos -n \
      || echo "Certificate check failed, keeping the current certificate."
  else
    echo "No letsencrypt email is set"
  fi
else
  echo "Checking if letsencrypt email is set."
  if [ -z $LE_EMAIL ]; then
    echo "No letsencrypt email is set using http config."
    cp .github/docker/default.conf /etc/nginx/http.d/panel.conf
  else
    echo "writing ssl config"
    cp .github/docker/default_ssl.conf /etc/nginx/http.d/panel.conf
    echo "updating ssl config for domain"
    sed -i "s|<domain>|$(echo $APP_URL | sed 's~http[s]*://~~g')|g" /etc/nginx/http.d/panel.conf
    echo "generating certs"
    certbot certonly -d $(echo $APP_URL | sed 's~http[s]*://~~g')  --standalone -m $LE_EMAIL --agree-tos -n
  fi
  echo "Removing the default nginx config"
  rm -rf /etc/nginx/http.d/default.conf
fi

if [[ -z $DB_PORT ]]; then
  echo -e "DB_PORT not specified, defaulting to 3306"
  DB_PORT=3306
fi

## shared folder for the updater service (update requests and progress)
mkdir -p /app/updater && chown nginx: /app/updater
## logo/favicon/background uploads (Settings -> Design), kept in the persistent var volume
mkdir -p /app/var/branding && chown -R nginx: /app/var/branding
## phpMyAdmin sessions and temporary files, only readable by php-fpm (see .github/docker/phpmyadmin)
mkdir -p /tmp/phpmyadmin/sessions /tmp/phpmyadmin/tmp && chown -R nginx: /tmp/phpmyadmin && chmod 700 /tmp/phpmyadmin /tmp/phpmyadmin/sessions /tmp/phpmyadmin/tmp

## check log folder permissions
echo "Checking log folder permissions."
if [ "$(stat -c %U:%G /app/storage/logs)" != "nginx" ]; then
  echo "Fixing log folder permissions."
  chown -R nginx: /app/storage/logs/
fi

## check for DB up before starting the panel
echo "Checking database status."
until nc -z -v -w30 $DB_HOST $DB_PORT
do
  echo "Waiting for database connection..."
  # wait for 1 seconds before check again
  sleep 1
done

## make sure the db is set up
echo -e "Migrating and Seeding D.B"
php artisan migrate --seed --force

## start cronjobs for the queue
echo -e "Starting cron jobs."
crond -L /var/log/crond -l 5

echo -e "Starting supervisord."
exec "$@"
