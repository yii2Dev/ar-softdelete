# This will output the help for each task. thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
help: ## Show this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

DOCKER_RUN=docker run --rm --interactive --tty --volume $(shell pwd):/app --user $(id -u):$(id -g)
#COMPOSER_CONTAINER=docker run --rm --interactive --tty --volume $(shell pwd):/app --user $(id -u):$(id -g) composer
#YII2_CONTAINER=docker run --rm --interactive --tty --volume $(shell pwd):/app --user $(id -u):$(id -g) yiisoftware/yii2-php:8.1-fpm

all: help

composer-install: ## install composer packages
	@$(DOCKER_RUN) yiisoftware/yii2-php:8.1-fpm composer install

run-tests: ## run tests
	@$(DOCKER_RUN) yiisoftware/yii2-php:8.1-fpm php vendor/bin/phpunit /app/tests

run-cs-fixer: ## Run php-cs-fixer and fix
	@$(DOCKER_RUN) --platform linux/arm64/v8 miroff/php-cs-fixer:3.11.0 php-cs-fixer fix -v /app

pre-commit: composer-install run-cs-fixer run-tests ## Run pre-commit checks
