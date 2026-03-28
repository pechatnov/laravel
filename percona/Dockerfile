FROM --platform=linux/amd64 percona:8.0

LABEL org.opencontainers.image.source="local/laravel-docker"

COPY my.cnf /etc/mysql/conf.d/my.cnf

USER root

CMD ["mysqld"]

EXPOSE 3306
