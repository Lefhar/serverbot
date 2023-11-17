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

# Récupérer les sites depuis la base de données où le champ "file" est égal à 1
sites=$(mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -D $DB_NAME -e "SELECT id, ip, port, domaine, file FROM website WHERE file = 1" | tail -n +2)

IFS=$'\n'

for site in $sites; do
    id=$(echo "$site" | cut -f1)
    ip=$(echo "$site" | cut -f2)
    port=$(echo "$site" | cut -f3)
    domaine=$(echo "$site" | cut -f4)
    file=$(echo "$site" | cut -f5)

    if [ -n "$id" ] && [ -n "$domaine" ]; then
        fichier_config=$(echo "$domaine" | tr '.' '-' | sed 's/\(.*\)/\L\1/').conf
        echo "Domaine: $domaine"
        echo "Fichier: $fichier_config"

        # Vérifier si le fichier de configuration existe déjà
        if [ -e "$APACHE_CONFIG_DIR/$fichier_config" ]; then
            echo "Le fichier de configuration $fichier_config existe déjà. Mise à jour en cours..."

            # Mettre à jour le contenu de la configuration
            CONFIG_CONTENT="<VirtualHost *:80>\n"
            CONFIG_CONTENT+="    ServerAdmin postmaster@$domaine\n"
            CONFIG_CONTENT+="    ServerName $domaine\n"
            CONFIG_CONTENT+="    ProxyPass / http://$ip:$port/\n"
            CONFIG_CONTENT+="    ProxyPassReverse / http://$domaine:$port/\n"
            CONFIG_CONTENT+="    ProxyPreserveHost On\n"
            CONFIG_CONTENT+="    RemoteIPHeader X-Forwarded-For\n"
            CONFIG_CONTENT+="</VirtualHost>\n"

            # Mettre à jour le fichier de configuration Apache
            if echo -e "$CONFIG_CONTENT" > "$APACHE_CONFIG_DIR/$fichier_config"; then
                echo "Fichier de configuration mis à jour avec succès."
                # Recharger la configuration d'Apache
                systemctl reload apache2
                echo "Configuration Apache rechargée avec succès."
                # Mettre à jour le champ "file" à 0 dans la base de données
                if mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -D $DB_NAME -e "UPDATE website SET file = 0 WHERE id = $id"; then
                    echo "Champ 'file' mis à jour dans la base de données."
                else
                    echo "Erreur lors de la mise à jour du champ 'file' dans la base de données."
                fi
            else
                echo "Erreur lors de la mise à jour du fichier de configuration."
            fi
        else
            if [ -n "$ip" ] && [ -n "$port" ]; then
                # Créer le contenu de la configuration
                CONFIG_CONTENT="<VirtualHost *:80>\n"
                CONFIG_CONTENT+="    ServerAdmin postmaster@$domaine\n"
                CONFIG_CONTENT+="    ServerName $domaine\n"
                CONFIG_CONTENT+="    ProxyPass / http://$ip:$port/\n"
                CONFIG_CONTENT+="    ProxyPassReverse / http://$domaine:$port/\n"
                CONFIG_CONTENT+="    ProxyPreserveHost On\n"
                CONFIG_CONTENT+="    RemoteIPHeader X-Forwarded-For\n"
                CONFIG_CONTENT+="</VirtualHost>\n"

                # Créer le fichier de configuration Apache
                if echo -e "$CONFIG_CONTENT" > "$APACHE_CONFIG_DIR/$fichier_config"; then
                    echo "Fichier de configuration créé avec succès."
                    # Recharger la configuration d'Apache
                    systemctl reload apache2
                    echo "Configuration Apache rechargée avec succès."

                    # Générer le certificat SSL avec Certbot
                certbot certonly --standalone --non-interactive --agree-tos --email s.lefebvre907@laposte.net -d $domaine --renew-by-default --http-01-port 4443


                    # Créer le fichier de configuration SSL
                # ssl_config="<VirtualHost *:4443>\n"
                # ssl_config+="    ServerAdmin postmaster@$domaine\n"
                # ssl_config+="    ServerName $domaine\n"
                # ssl_config+="    ProxyPass / https://$ip:443/\n"
                # ssl_config+="    ProxyPassReverse / https://$domaine:4443/\n"
                # ssl_config+="    ProxyPreserveHost On\n"
                # ssl_config+="    RemoteIPHeader X-Forwarded-For\n"
                # ssl_config+="    SSLEngine on\n"
                # ssl_config+="    SSLCertificateFile /etc/letsencrypt/live/$domaine/fullchain.pem\n"
                # ssl_config+="    SSLCertificateKeyFile /etc/letsencrypt/live/$domaine/privkey.pem\n"
                # ssl_config+="</VirtualHost>\n"


                #     # Enregistrer la configuration SSL
                #     echo -e "$ssl_config" > "$APACHE_CONFIG_DIR/$fichier_config-ssl.conf"

                    # Recharger la configuration d'Apache pour activer le certificat SSL
                    systemctl reload apache2

                    # ... (votre code de mise à jour de base de données)
                else
                    echo "Erreur lors de la création du fichier de configuration."
                fi
            else
                echo "Erreur: IP ou port manquant."
            fi
        fi
    else
        echo "Erreur: ID ou domaine manquant."
    fi
done
