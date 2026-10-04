FROM php:8.2-apache

# Enable Apache mod_rewrite module for URL routing
RUN a2enmod rewrite

# Install curl extension for Google Sheets sync
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Inject routing rules directly into Apache config (works even if .htaccess was not uploaded!)
RUN printf '<Directory /var/www/html>\n\tOptions -Indexes +FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n\tRewriteEngine On\n\tRewriteCond %%{REQUEST_FILENAME} !-f\n\tRewriteCond %%{REQUEST_FILENAME} !-d\n\tRewriteRule ^ index.php [QSA,L]\n</Directory>\n' > /etc/apache2/conf-available/override.conf \
    && a2enconf override

# Copy project code to web root
COPY . /var/www/html/

# If project was uploaded inside a subfolder sis_calif, copy its contents to web root
RUN if [ -d "/var/www/html/sis_calif" ]; then cp -rn /var/www/html/sis_calif/* /var/www/html/ 2>/dev/null || true; fi

# Configure permissions for JSON storage directory
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

ENV PORT=80
EXPOSE 80

CMD ["apache2-foreground"]
