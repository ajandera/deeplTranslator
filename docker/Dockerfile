FROM php:7.1-apache
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git

COPY ./php.ini /usr/local/etc/php

ADD ./apache.conf /etc/apache2/sites-available/
RUN ln -s /etc/apache2/sites-available/apache.conf /etc/apache2/sites-enabled/
RUN a2enmod rewrite
