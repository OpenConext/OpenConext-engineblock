#!/usr/bin/env bash
uid=$(id -u)
gid=$(id -g)

printf "UID=${uid}\nGID=${gid}\nCOMPOSE_PROJECT_NAME=eb" > .env

docker-compose up -d

docker-compose exec -T php-fpm.vm.openconext.org bash -c '
  composer install --prefer-dist -n -o && \
  ./app/console cache:clear --env=test && \
    SYMFONY_ENV=test composer prepare-env && \
  cd theme && npm ci && npm run build
'
