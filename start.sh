#!/bin/sh
# Script de arranque para Render
# Render asigna el puerto en la variable $PORT

echo "Starting PHP server on port $PORT..."
php -S 0.0.0.0:$PORT -t .
