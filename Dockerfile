FROM docker.huixiaoer.net/php-dev/centos_php72:latest

MAINTAINER liuhuan

LABEL version="1.0"

LABEL description="huixiaoer_laravel"

WORKDIR /home/www

ADD ./ /home/www/huixiaoer_laravel

RUN sed -i "s/short_open_tag = Off/short_open_tag = On/g" /etc/php.ini; \
    chown www:www -R /home/www/huixiaoer_laravel; \
    rm -f /etc/nginx/vhost/*.conf

ADD ./demo.huixiaoer.com.conf /etc/nginx/vhost

EXPOSE 80 443