FROM php:7.4-apache

# Install required composer extensions
RUN apt-get update && apt-get install -qqy libfreetype6-dev libjpeg62-turbo-dev libpng-dev cron libaio-dev libmcrypt-dev libzip-dev libpq-dev unzip --no-install-recommends
RUN apt-get install supervisor cron tzdata -qqy 
RUN docker-php-ext-install zip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd opcache zip

# xdebug, if you want to debug
#RUN pecl install xdebug && docker-php-ext-enable xdebug

# PHP composer
#RUN curl -sS https://getcomposer.org/installer | php --  --install-dir=/usr/bin --filename=composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
# apache configurations, mod rewrite
RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load


# Oracle instantclient

# copy oracle files
# ADD oracle/instantclient-basic-linux.x64-12.1.0.2.0.zip /tmp/
ADD https://download.oracle.com/otn_software/linux/instantclient/211000/instantclient-basic-linux.x64-21.1.0.0.0.zip /tmp/
# ADD oracle/instantclient-sdk-linux.x64-12.1.0.2.0.zip /tmp/
ADD https://download.oracle.com/otn_software/linux/instantclient/211000/instantclient-sdk-linux.x64-21.1.0.0.0.zip /tmp/
# ADD oracle/instantclient-sqlplus-linux.x64-12.1.0.2.0.zip /tmp/
ADD https://download.oracle.com/otn_software/linux/instantclient/211000/instantclient-sqlplus-linux.x64-21.1.0.0.0.zip /tmp/

# unzip them
RUN unzip /tmp/instantclient-basic-linux.x64-*.zip -d /usr/local/ \
    && unzip /tmp/instantclient-sdk-linux.x64-*.zip -d /usr/local/ \
    && unzip /tmp/instantclient-sqlplus-linux.x64-*.zip -d /usr/local/

# install oci8
RUN ln -s /usr/local/instantclient_*_1 /usr/local/instantclient \
    && ln -s /usr/local/instantclient/sqlplus /usr/bin/sqlplus

RUN docker-php-ext-configure oci8 --with-oci8=instantclient,/usr/local/instantclient \
    && docker-php-ext-install oci8 \
    && echo /usr/local/instantclient/ > /etc/ld.so.conf.d/oracle-insantclient.conf \
    && ldconfig

# Copy App Files
COPY . /var/www/html/pembayaran-pbb

# Install required app files
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux
RUN cd /var/www/html/pembayaran-pbb && composer install --no-scripts --no-dev
#change owner
RUN cd /var/www/html/pembayaran-pbb && cp .env.example .env \
    && chown -R www-data:www-data /var/www/html \
    && composer remove --dev facade/ignition \
    && php generate-image-version.php \
    && php artisan key:generate

# Enable mod_rewrite
RUN a2enmod rewrite

#laravel root add -> public
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

ENV TZ="Asia/Jakarta"


# Copy hello-cron file to the cron.d directory
#COPY cron-job /etc/cron.d/cron-job

# Give execution rights on the cron job
#RUN chmod 0744 /etc/cron.d/cron-job

# Apply cron job
#RUN crontab /etc/cron.d/cron-job

# Create the log file to be able to run tail
#RUN touch /var/log/cron-job.log

# Run the command on container startup
#CMD cron && tail -f /var/log/cron-job.log

# Run the command on container startup
#CMD cron && tail -f /var/log/cron-job.log
#CMD bash -c "cron && sleep 1 &&  apache2-foreground"

