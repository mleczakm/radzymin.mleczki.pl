# syntax=docker/dockerfile:1

FROM php:8.5-cli-alpine AS php-base

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# install-php-extensions resolves and builds the current stable ext-swoole
# (and friends) non-interactively — see https://github.com/mlocati/docker-php-extension-installer
# io_uring support is skipped: it's auto-enabled whenever liburing is present, but the
# io_uring syscalls are blocked (EPERM) under many container seccomp profiles (including
# this build's sandbox and, likely, the target Mikrus host), which crash-loops every worker.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/install-php-extensions
RUN chmod +x /usr/local/bin/install-php-extensions \
    && IPE_SWOOLE_WITHOUT_IOURING=1 install-php-extensions swoole pdo_sqlite intl opcache

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

COPY --from=build /app /app
# /app/db is the named volume mount point used by the shared ansible app_deploy role.
RUN mkdir -p /app/db && chown -R app:app /app/db

USER app
ENV APP_ENV=prod
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://127.0.0.1:80/zdrowie || exit 1

CMD ["php", "bin/server"]
