FROM composer as vendor

WORKDIR /tmp/

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM php:8-cli-alpine

WORKDIR app
COPY --from=vendor /tmp/vendor/ vendor/
COPY bin bin
COPY src src
EXPOSE 8080
CMD ["php", "bin/server.php"]