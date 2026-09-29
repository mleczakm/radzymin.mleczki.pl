# syntax=docker/dockerfile:1

FROM php:8.5-cli-alpine AS php-base

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# io_uring support is skipped: it's auto-enabled whenever liburing is present, but the
# io_uring syscalls are blocked (EPERM) under many container seccomp profiles (including
# this build's sandbox and, likely, the target Mikrus host), which crash-loops every worker.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/install-php-extensions
# The PDO SQLite coroutine hook is optional in Swoole. Build it explicitly, and keep
# io_uring disabled because its syscalls are blocked by some container hosts.
RUN chmod +x /usr/local/bin/install-php-extensions \
    && IPE_SWOOLE_WITHOUT_IOURING=1 install-php-extensions pdo_sqlite intl opcache sockets

RUN apk add --no-cache sqlite-libs \
    && apk add --no-cache --virtual .swoole-build-deps $PHPIZE_DEPS linux-headers openssl-dev sqlite-dev \
    && CPPFLAGS="-DHAVE_FUTEX" pecl install --configureoptions 'enable-sockets="yes" enable-swoole-sqlite="yes" enable-iouring="no" enable-brotli="no"' swoole \
    && docker-php-ext-enable swoole \
    && apk del .swoole-build-deps

WORKDIR /app

# ---- build: install deps + optimize autoloader -----------------------------
FROM php-base AS build

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-progress --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

# ---- dev: hot-reload against a bind-mounted source tree ---------------------
FROM php-base AS dev

ENV APP_ENV=dev
EXPOSE 8080
CMD ["sh", "-c", "composer install --no-interaction && php bin/server"]

# ---- prod: minimal runtime image --------------------------------------------
FROM php-base AS prod

RUN addgroup -g 1000 app && adduser -D -u 1000 -G app app

COPY .docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY --from=build /app /app
# /app/db is the named volume mount point used by the shared ansible app_deploy role.
RUN mkdir -p /app/db && chown -R app:app /app/db

USER app
ENV APP_ENV=prod
EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://127.0.0.1:8080/zdrowie || exit 1

CMD ["php", "bin/server"]
