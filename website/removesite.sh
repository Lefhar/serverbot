#!/bin/bash

# Charger les variables d'environnement depuis le fichier .env
export $(grep -v '^#' .env | xargs)

# Extraire les informations de la chaîne DATABASE_URL
DB_USER=$(echo $DATABASE_URL | awk -F[@:/] '{print $2}')
DB_PASS=$(echo $DATABASE_URL | awk -F[@:/] '{print $3}')
DB_HOST=$(echo $DATABASE_URL | awk -F[@:/] '{print $4}')
DB_PORT=$(echo $DATABASE_URL | awk -F[@:/] '{print $5}')
DB_NAME=$(echo $DATABASE_URL | awk -F[@:/] '{print $7}')

APACHE_CONFIG_DIR="/etc/apache2/sites-enabled"

# Récupérer les sites depuis la base de données où le champ "remove" est égal à 1
sites=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -D $DB_NAME -e "SELECT id, domaine FROM website WHERE remove = 1" | tail -n +2)

IFS=$'\n'
for site in $sites; do
    site_id=$(echo $site | cut -f1)
    domaine=$(echo $site | cut -f2)

    # Remplacer les points par des tirets pour le fichier de configuration
    config_file="${domaine//./-}.conf"
    ssl_config_file="${domaine//./-}-le-ssl.conf"

    # Supprimer les fichiers de configuration Apache
    if [ -f "$APACHE_CONFIG_DIR/$config_file" ]; then
        rm "$APACHE_CONFIG_DIR/$config_file"
        echo "Fichier de configuration $config_file supprimé."
    else
        echo "Fichier de configuration $config_file non trouvé."
    fi

    if [ -f "$APACHE_CONFIG_DIR/$ssl_config_file" ]; then
        rm "$APACHE_CONFIG_DIR/$ssl_config_file"
        echo "Fichier de configuration $ssl_config_file supprimé."
    else
        echo "Fichier de configuration $ssl_config_file non trouvé."
    fi

    # Supprimer les certificats SSL avec Certbot
    echo "Suppression des certificats SSL pour $domaine avec Certbot..."
    certbot delete --cert-name "$domaine" 2>&1

    # Vérifier si les certificats ont été supprimés correctement
    if [ ! -f "$APACHE_CONFIG_DIR/$config_file" ] && [ ! -f "$APACHE_CONFIG_DIR/$ssl_config_file" ]; then
        echo "Les fichiers ont été supprimés correctement."
        # Supprimer la ligne dans la base de données
        mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -D $DB_NAME -e "DELETE FROM website WHERE id = $site_id"
          systemctl reload apache2
    else
        echo "Les fichiers n'ont pas été supprimés correctement."
    fi
done
