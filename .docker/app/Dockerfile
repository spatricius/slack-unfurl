FROM php:8.1.2-fpm

ARG uuid=1000
ARG gid=www
ARG without_xdebug
ENV WITHOUT_XDEBUG=$without_xdebug

# Copy configuration file:
COPY ./php.ini /usr/local/etc/php/conf.d/php.ini

RUN echo 'deb [trusted=yes] https://repo.symfony.com/apt/ /' | tee /etc/apt/sources.list.d/symfony-cli.list
RUN apt-get update && apt-get upgrade -yqq
RUN apt-get install -yqq git zip unzip vim sudo symfony-cli libpq-dev

# Enable PHP Extentions:
RUN docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer

# Add simple user
RUN adduser --uid=$uuid www

# Install xdebug:
COPY --chown=${uuid}:${gid} ./install* /tmp/
COPY ./xdebug.ini /tmp/xdebug.ini
RUN chmod a+x /tmp/install*
RUN /tmp/install-xdebug.sh

USER $uuid

CMD ["/bin/bash","/tmp/install.sh"]
