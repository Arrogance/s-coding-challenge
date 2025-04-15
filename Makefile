.PHONY: all build build-no-cache up stop down bash install update migrations style phpunit phpunit-coverage setup-api-tests test-api logs phpunit-dev

# Select env file: .env.local takes precedence
ENV_FILE := .env
ifeq ($(wildcard .env.local), .env.local)
  ENV_FILE := .env.local
endif

# Extract project name from env file
PROJECT_NAME := $(shell grep -m 1 '^APP_NAME=' $(ENV_FILE) | cut -d '=' -f2)

# Compose files
COMPOSE_FILES := -f compose.yaml
ifneq ($(wildcard compose.override.yaml),)
  COMPOSE_FILES += -f compose.override.yaml
endif

# Docker Compose commands
DC_CMD     = docker compose $(COMPOSE_FILES) -p $(PROJECT_NAME) --env-file $(ENV_FILE)
DC_RUN_PHP = $(DC_CMD) exec app

# Default target
all: build up install migrations

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
	@$(DC_RUN_PHP) env XDEBUG_MODE=off bin/console doctrine:migrations:migrate --no-interaction

style:
	@$(DC_RUN_PHP) env XDEBUG_MODE=off PHP_CS_FIXER_IGNORE_ENV=true vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --using-cache=no -vvv

logs:
	@$(DC_RUN_PHP) tail -f var/log/dev.log

# Tests
phpunit:
	@$(DC_RUN_PHP) env XDEBUG_MODE=off APP_ENV=test vendor/bin/phpunit tests

phpunit-dev:
	@$(DC_RUN_PHP) env XDEBUG_MODE=off APP_ENV=test vendor/bin/phpunit tests --group dev
