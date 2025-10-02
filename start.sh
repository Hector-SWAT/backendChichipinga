#!/bin/bash
# Script de arranque para Render

PORT=${PORT:-8080}
echo "Starting La Chichipinga API on port $PORT..."
echo "Files in directory:"
ls -la

php -S 0.0.0.0:$PORT -t .