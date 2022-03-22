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

# abrindo portas
EXPOSE 80
EXPOSE 2114
EXPOSE 8000

# iniciando supervisor
ENTRYPOINT ["supervisord"]
