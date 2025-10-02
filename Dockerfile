FROM php:8.2-cli

WORKDIR /app
COPY . .

# Usar el puerto que Render asigne
CMD ["sh", "-c", "php -S 0.0.0.0:$PORT -t ."]
