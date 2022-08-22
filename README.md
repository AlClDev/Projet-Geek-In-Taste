# Geek In Taste

## Projet de soutenance


### Installation

```
git clone https://github.com/Adlbanistophe/Geek_In_Taste.git 
```


###### Modifier les paramètres d'environnement dans le fichier **.env** pour les faire correspondre à votre environnement (accès BDD, clés Google Recaptcha ...) :

```
# Accès BDD à modifier
DATABASE_URL="mysql://db_user/db_password:@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"

# Clés Google Recaptcha à modifier
GOOGLE_RECAPTCHA_SITE_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxx
GOOGLE_RECAPTCHA_PRIVATE_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxx

# Clés pour authentification Google
GOOGLE_CLIENT_ID=xxxxxxxxxxxxxxxxxxxxxxxxxx
GOOGLE_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxx

# Pour activer le mailer Symfony
MAILER_DSN=null://null
```


###### Déplacer le terminal dans le dossier cloné du projet :
```
cd Geek_In_Taste
```


###### Taper les commandes : 
```
composer install
symfony console doctrine:database:create

Depuis le 27/05 :
symfony console make:migration
symfony console doctrine:migration:migrate

Depuis le 01/06 :
symfony console doctrine:fixtures:load
```

###### Fixture information
Les fixtures créeront :
* 1 compte admin: ( email: admin@a.a, password: Azerty8/ )
* 5 comptes utilisateurs ( email: aléatoire, password: Azerty8/ )
* 15 nouvelles recettes
* 4 catégories
* Entre 0 et 10 commentaires