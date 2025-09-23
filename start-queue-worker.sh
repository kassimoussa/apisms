#!/bin/bash

# Script pour démarrer le queue worker Laravel
# Ce script sera utilisé par launchd

cd /Users/ctd-dsi-kassim/Documents/sites/apisms

# Attendre que l'application soit prête
sleep 5

# Démarrer le queue worker avec redémarrage automatique
while true; do
    echo "$(date): Starting Laravel queue worker..."
    php artisan queue:work --queue=bulk-sms --timeout=600 --tries=3 --sleep=3 --memory=512
    
    # Si le worker s'arrête, attendre 5 secondes avant de redémarrer
    echo "$(date): Queue worker stopped, restarting in 5 seconds..."
    sleep 5
done