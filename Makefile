.PHONY: all build build-no-cache up stop down bash install update migrations style phpunit phpunit-coverage

# Paths and flags
COMPOSE_FILE = compose.yaml:compose.override.yaml
PROJECT_NAME := $(shell grep -m 1 '^APP_NAME=' .env | cut -d '=' -f2)

# Docker Compose commands
DC_CMD     = COMPOSE_FILE=$(COMPOSE_FILE) docker compose -p $(PROJECT_NAME)
DC_RUN_PHP = $(DC_CMD) exec --user 1000:33 app

# Default target
all: build up install

# Build containers
build:
	@$(DC_CMD) build

build-no-cache:
	@$(DC_CMD) build --no-cache

# Start / Stop / Teardown
up:
	@$(DC_CMD) up --detach

stop:
	@$(DC_CMD) stop

down:
	@$(DC_CMD) down

# PHP commands within the container
bash:
	@$(DC_RUN_PHP) bash

install:
	@$(DC_RUN_PHP) env XDEBUG_MODE=off composer install

update:
	@$(DC_RUN_PHP) env XDEBUG_MODE=off composer update

migrations:
	@$(DC_RUN_PHP) env XDEBUG_MODE=off bin/console doctrine:schema:update --force --complete

style:
	@$(DC_RUN_PHP) env XDEBUG_MODE=off PHP_CS_FIXER_IGNORE_ENV=true vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --using-cache=no -vvv

logs:
	@$(DC_RUN_PHP) tail -f var/log/dev.log

phpunit:
	@$(DC_RUN_PHP) env XDEBUG_MODE=off bin/phpunit tests

phpunit-coverage:
	@$(DC_RUN_PHP) env XDEBUG_MODE=coverage bin/phpunit tests
