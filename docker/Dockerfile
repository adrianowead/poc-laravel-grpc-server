#=======================#
# composer já instalado #
#=======================#
FROM adrianowead/viware-base-php:1.1.0

# instalando compilador protobuf
RUN apt update
RUN apt install -y \
    protobuf-compiler

# copiando configs do apache
COPY docker/apache/* /etc/apache2/sites-available/

COPY docker/supervisor/* /etc/supervisor/conf.d/

RUN usermod -a -G sudo www-data

RUN chmod -R 775 /var/www/html

# ajustando permissão da pasta para o apache
# e habilitando alguns módulos
RUN chown -R www-data:www-data /var/www/html

RUN a2dissite 000-default.conf
RUN a2dissite default-ssl.conf

RUN a2ensite vhost-grpc-server.conf
RUN a2ensite vhost-grpc-client-a.conf

WORKDIR /tmp

RUN wget https://github.com/spiral/roadrunner-binary/releases/download/v2.7.0/protoc-gen-php-grpc-2.7.0-linux-amd64.tar.gz && \
    tar -xvf protoc-gen-php-grpc-2.7.0-linux-amd64.tar.gz && \
    cp protoc-gen-php-grpc-2.7.0-linux-amd64/protoc-gen-php-grpc /usr/bin/ && \
    chmod +x /usr/bin/protoc-gen-php-grpc

WORKDIR /var/www/html

# abrindo portas
EXPOSE 80
EXPOSE 2114
EXPOSE 8000

# iniciando supervisor
ENTRYPOINT ["supervisord"]
