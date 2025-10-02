FROM php:8.2-cli

# Copia los archivos al contenedor
WORKDIR /app
COPY . .

# Comando para iniciar servidor PHP
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
