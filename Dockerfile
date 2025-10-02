# Usamos PHP CLI 8.2
FROM php:8.2-cli

# Carpeta de trabajo
WORKDIR /app

# Copiamos todos los archivos al contenedor
COPY . .

# Damos permisos de ejecución al script de arranque
RUN chmod +x start.sh

# Comando para iniciar el servidor PHP usando el puerto de Render
CMD ["./start.sh"]
