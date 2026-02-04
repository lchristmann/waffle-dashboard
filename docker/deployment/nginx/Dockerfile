ARG VERSION

FROM leanderchristmann/waffle-dashboard:${VERSION:-0.0.0} AS php-fpm-source

FROM docker.angie.software/angie:latest

# Copy the public directory contents straight from the previously built leanderchristmann/waffle-dashboard Image
COPY --from=php-fpm-source /var/www/public /var/www/public
COPY ./docker/deployment/nginx /etc/angie/http.d/

WORKDIR /var/www/public

EXPOSE 80
CMD ["angie", "-g", "daemon off;"]
