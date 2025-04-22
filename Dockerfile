FROM php:8.2-apache

# Change the listening port to 10000 for Render
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

# Copy your site files to the Apache web root
COPY . /var/www/html/

# Expose port 10000
EXPOSE 10000
