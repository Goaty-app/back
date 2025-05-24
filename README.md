# Goaty-App

## Pour commencer

1. Si ce n'est pas déjà fait, [installez Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)[1]
2. Exécutez `docker compose build --no-cache` pour construire des images fraîches
3. Exécutez `docker compose up --pull always -d --wait` pour configurer et démarrer un nouveau projet Symfony
4. Ouvrez `https://localhost` dans votre navigateur web préféré et [acceptez le certificat TLS auto-généré](https://stackoverflow.com/a/15076602/1352334)
5. Exécutez `docker compose down --remove-orphans` pour arrêter les conteneurs Docker.

## Configuration du projet

Copiez .env vers .env.local
```sh
cp .env .env.local
```

Générez les clés privées et publiques
```sh
docker compose exec php php bin/console lexik:jwt:generate-keypair
```

Mettez à jour le schéma de la base de données et les données initiales
```sh
docker compose exec php bin/console doctrine:schema:update --force
docker compose exec php bin/console doctrine:fixtures:load
```

## Développement

Formatez le code selon les directives PSR
```sh
docker compose exec php ./vendor/bin/php-cs-fixer fix src
```

Vider le cache
```sh
docker compose exec php bin/console cache:clear
```

## Base de données

![Base de données](Database.png)
