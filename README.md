# ServeurBot

Site personnel de gestion électrique de serveur et d'informations.

En raison de la présence de serveurs à domicile, j'ai entrepris de créer une interface de gestion dédiée.

Cette interface me permet de superviser un appareil IP Power Switch et de gérer les serveurs via SSH2. Les identifiants sont cryptés à l'aide d'une clé de chiffrement.

Vous pouvez utiliser cette interface pour administrer vos serveurs, sites, etc. Elle doit être installée sur un serveur principal, par exemple, un serveur de type proxy. Il y a un script shell à configurer à cet effet avec vos identifiants de base de données.

L'ajout d'un site déclenchera automatiquement la création du vHost ainsi que la génération du certificat SSL grâce à Let's Encrypt.

Ce site a été réalisé avec Symfony 5.4.

N'oubliez pas d'exécuter la commande suivante :
```bash
chmod +x newsite.sh
chmod +x removesite.sh
