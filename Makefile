.PHONY: setup init

DOCKER=@docker-compose
DOCKER_PHP=$(DOCKER) exec php

setup: docker-up composer

version:
	$(DOCKER_PHP) php -v

composer:
	$(DOCKER_PHP) composer install

test:
	$(DOCKER_PHP) vendor/bin/phpunit

coverage:
	$(DOCKER_PHP) env XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-xml=var/coverage-xml/ --log-junit=var/coverage-xml/junit.xml --coverage-html=var/coverage/

linter: stan cs

stan:
	$(DOCKER_PHP) vendor/bin/phpstan analyse

cs:
	$(DOCKER_PHP) vendor/bin/php-cs-fixer fix -v --dry-run

cs-fix:
	$(DOCKER_PHP) vendor/bin/php-cs-fixer fix -v

shell:
	$(DOCKER_PHP) bash

logs:
	$(DOCKER) logs -f php

# Docker
docker-up:
	$(DOCKER) up -d --force-recreate --remove-orphans

docker-down:
	$(DOCKER) down -v


