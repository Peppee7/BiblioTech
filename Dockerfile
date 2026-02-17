FROM php:8.2-apache

# Installazione estensioni PDO per MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Installazione di msmtp (client SMTP leggero)
RUN apt-get update && apt-get install -y msmtp

# Configurazione di PHP per usare Mailpit
# Nota: 'mailpit' è il nome del servizio definito nel docker-compose
RUN echo "sendmail_path = \"/usr/bin/msmtp -t --host=mailpit --port=1025\"" > /usr/local/etc/php/conf.d/mail.ini

RUN a2enmod rewrite