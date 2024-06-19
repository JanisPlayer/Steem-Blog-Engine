FROM debian:bullseye-slim

USER root

# Debian aktualisieren und notwendige Pakete installieren
RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
    mariadb-client wget curl \
    ca-certificates lsb-release apt-transport-https gnupg bsdmainutils software-properties-common

# PHP-Repository hinzufügen
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list \
    && curl -fsSL https://packages.sury.org/php/apt.gpg | apt-key add - \
    && apt-get update -qq

# PHP 8.0 und notwendige Erweiterungen installieren
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y php8.0 php8.0-common php8.0-cli php8.0-fpm \
    php8.0-mysql php8.0-curl php8.0-xml php8.0-mbstring \
    php8.0-intl php8.0-redis php8.0-zip php8.0-sqlite3 \
    php-imagick

# Standardverzeichnis festlegen
WORKDIR /var/app

# Den Prozess ausführen, den dieser Container bereitstellen wird
CMD tail -f /dev/null
