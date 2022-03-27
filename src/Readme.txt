# webServicesProject2

*Projet en construction*

*commandes utiles*
# ws-authjwt
# composer create-project symfony/website-skeleton ws-authjwt
# php -S localhost:8000 -t public/
# composer require api
# composer require symfony/orm-pack
# php bin/console doctrine:database:create
# php bin/console make:entity
# php bin/console make:migration
# php bin/console doctrine:migration:migrate   
# composer require --dev doctrine/doctrine-fixtures-bundle
# composer require fakerphp/faker
# php bin/console doctrine:fixtures:load
# php bin/console debug:route

Pour create JWT:
Installation:
- composer require lexik/jwt-authentication-bundle
- mkdir config/jwt
- Créer deux clees, depuis un console openssl, exemble:
    * genrsa -out C:\Users\thanh\Desktop\YNOV\YNOV-M2\WEB-SERVICE\webServiceProjetc2-V2/config/jwt/private.pem -aes256 4096
    * pkey -in C:\Users\thanh\Desktop\YNOV\YNOV-M2\WEB-SERVICE\webServiceProjetc2-V2/config/jwt/private.pem -out C:\Users\thanh\Desktop\YNOV\YNOV-M2\WEB-SERVICE\webServiceProjetc2-V2/config/jwt/public.pem -pubout
Entrée un mot de passe du clé privée pour le clé publique

Saisir le mot de passe dans .env

###> lexik/jwt-authentication-bundle ###
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PASSPHRASE=authjwt
###< lexik/jwt-authentication-bundle ###

#composer pour creer le refresh token
composer require doctrine/orm doctrine/doctrine-bundle gesdinet/jwt-refresh-token-bundle
php bin/console make:migration
php bin/console doctrine:migrations:migrate



# Useful Commands
Revoke all invalid tokens
If you want to revoke all invalid (datetime expired) refresh tokens you can execute:

php bin/console gesdinet:jwt:clear
The command optionally accepts a date argument which will delete all tokens older than the given time. This can be any value that can be parsed by the DateTime class.

php bin/console gesdinet:jwt:clear 2015-08-08
We recommend executing this command as a cronjob to remove invalid refresh tokens on an interval.

Revoke a token
If you want to revoke a single token you can use this command:

php bin/console gesdinet:jwt:revoke TOKEN