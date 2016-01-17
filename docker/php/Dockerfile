FROM php:5.6-cli

ENV DEBIAN_FRONTEND noninteractive

RUN \
  apt-get update && \
  apt-get install -y php5-redis && \
  cp /usr/lib/php5/20131226/redis.so /usr/local/lib/php/extensions/no-debug-non-zts-20131226 && \
  apt-get clean && \
  rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY php.ini /usr/local/etc/php/

VOLUME /data
WORKDIR /data

EXPOSE 8080     

ENTRYPOINT [ "php", "-S", "0.0.0.0:8080", "-t", "/data/public", "/data/public/router.php" ]
