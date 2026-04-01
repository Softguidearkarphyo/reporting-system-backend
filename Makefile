
default:
	@echo Please specify target name!
up:
	docker compose up -d
build:
	docker compose build --no-cache --force-rm
clear:
	docker rm -f $$(docker ps -a -q)
	docker image rm -f $$(docker images -q)
	docker volume rm -f $$(docker volume ls -q)
init:
	cp -n ./src/.env.example ./src/.env
	@make build
	@make up
	docker compose exec app chmod -R 777 storage bootstrap/cache
	@make composer-install
	docker compose exec app php artisan key:generate
	@make fresh-seed
remake:
	@make destroy
	@make init
stop:
	docker compose stop
down:
	docker compose down
restart:
	@make down
	@make up
rebuild:
	@make down
	@make build
	@make up
destroy:
	docker compose down --rmi all --volumes
destroy-volumes:
	docker compose down --volumes
ps:
	docker compose ps
logs:
	docker compose logs --follow
app:
	docker compose exec app bash

# laravel short commands
migrate:
	docker compose exec app php artisan migrate
fresh:
	docker compose exec app php artisan migrate:fresh
fresh-seed:
	docker compose exec app php artisan migrate:fresh --seed
composer-install:
	docker compose exec app composer install
dump-autoload:
	docker compose exec app composer dump-autoload
test:
	docker compose exec app php artisan test --exclude-group=broken
