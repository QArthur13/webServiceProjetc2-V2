# webServiceProjetc2-V2

Pour lancer le projet:

D'abord créer un fichier:
.env.local

Puis rentrer la BDD en fonction de votre logiciel de BDD
Exemple: pour mariadb: 
- DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=mariadb-10.5.8"

Une fois fais ça lancer ces deux commandes:
- composer install
- composer update

Après faite ça:
- php bin/console doctrine:database:create
- php bin/console make:migration
- php bin/console doctrine:migrations:migrate

Lancer aussi la fixtures, pour avoir déjà un compte Admin créer:
- php bin/console doctrine:fixture:load

Lancer le serveur avec cette commande:
- symfony serve

et voila, l'api fonctione
