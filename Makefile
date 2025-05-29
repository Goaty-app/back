.PHONY: down up db build schema fixtures cache clean

.DEFAULT_GOAL := clean

fix:
	docker compose exec php ./vendor/bin/php-cs-fixer fix

build:
	docker compose build --no-cache

down:
	docker compose down --remove-orphans

up:
	docker compose up --pull always -d --wait

db: down up
	docker compose exec php bin/console doctrine:database:drop --force --if-exists
	docker compose exec php bin/console doctrine:database:create

schema:
	docker compose exec php bin/console doctrine:schema:update --force

fixtures:
	docker compose exec php bin/console doctrine:fixtures:load --no-interaction

cache:
	docker compose exec php bin/console cache:clear

clean: db schema fixtures cache
	@echo "✅ Database reset, schema updated, fixtures loaded, and cache cleared successfully."
