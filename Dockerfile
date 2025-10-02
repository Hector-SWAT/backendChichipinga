# Usamos PHP con Apache para mejor compatibilidad
FROM php:8.2-apache

# Carpeta de trabajo
WORKDIR /var/www/html

# Copiamos todos los archivos al contenedor
COPY . .

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Configurar Apache para usar el puerto de Render
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Exponer el puerto (Render lo maneja automáticamente)
EXPOSE 8080

# Comando para iniciar Apache
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]