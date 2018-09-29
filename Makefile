
# Keep this help dry. For example, we don't document the task composer-install
# because the task init is already documented.
help:
	@printf "\033[33m Usage:\033[39m\n"
	@printf "  make COMMAND\n"
	@printf "\n"
	@printf "\033[33m gazr.io tasks:\033[39m\n"
	@printf "\033[32m   init              \033[39m   Bootstrap your application.\n"
	@printf "\033[32m   style             \033[39m   Check lint, code styling rules.\n"
	@printf "\033[32m   complexity        \033[39m   Cyclomatic complexity check.\n"
	@printf "\033[32m   format            \033[39m   Format code.\n"
	@printf "\033[32m   test              \033[39m   Shortcut to launch all the tests tasks.\n"
	@printf "\033[32m   test-unit         \033[39m   Launch unit tests.\n"
	@printf "\n"
	@printf "\033[33m other helpful tasks:\033[39m\n"
	@printf "\033[32m   check             \033[39m   Shortcut to launch 'make style complexity test'.\n"
	@printf "\033[32m   clean             \033[39m   Clean everything generated by the application.\n"
	@printf "\n"
	@printf "\033[33m composer tasks:\033[39m\n"
	@printf "\033[32m   composer          \033[39m   Print composer command.\n"
	@printf "\n"
	@printf "\033[33m Fine grained tasks:\033[39m\n"
	@printf "\033[32m   php-cs-fixer      \033[39m   Fix php code style issues.\n"
	@printf "\033[32m   php-parallel-lint \033[39m   Check php files.\n"
	@printf "\033[32m   phpmetrics        \033[39m   Provide various metrics about PHP code.\n"
	@printf "\033[32m   phpstan           \033[39m   PHP static analysis tool.\n"
	@printf "\033[32m   phpunit           \033[39m   Unit test for PHP files.\n"
	@printf "\n"

_FMT="    \033[38;5;106m%s\033[39m\n"
_WRN="    \033[38;5;208m%s\033[39m\n"

PHP_VERSION ?= 7.1
DOCKER_ORGS ?= juliendufresne
DOCKER_RUN_OPTS=--rm --name $@ --volume $(CURDIR):/app -u $(shell id -u):$(shell id -g)

###> gazr.io tasks ###
.PHONY: init
init: composer.lock

.PHONY: style
style: composer-validate php-parallel-lint php-cs-fixer-dry-run phpstan

.PHONY: complexity
complexity: phpmetrics

.PHONY: format
format: php-cs-fixer

.PHONY: test
test: test-unit

.PHONY: test-unit
test-unit: phpunit
###< gazr.io tasks ###

###> other helpful tasks ###
.PHONY: check
check: style complexity test

.PHONY: clean
clean: composer-clean
	@if [ -d reports ]; then\
		printf $(_FMT) "rm -r reports";\
						rm -r reports;\
	fi
###< other helpful tasks ###

###> composer tasks ###
COMPOSER_CMD=docker run $(DOCKER_RUN_OPTS) $(DOCKER_ORGS)/php:$(PHP_VERSION) composer --ansi

# If composer.json has changed, you need to update composer.lock
composer.lock: composer.json
	@if [ -f $@ ]; \
	then \
		printf $(_WRN) "The $< file has changed. Synchronizing vendor"; \
		rm composer.lock; \
	fi
	@$(MAKE) -s composer-install

vendor: composer.lock
	@$(MAKE) -s composer-install

.PHONY: composer
composer:
	@printf $(_FMT) "$(COMPOSER_CMD)"

.PHONY: composer-clean
composer-clean:
	@if [ -f composer.lock ]; then\
		printf $(_FMT) "rm composer.lock";\
						rm composer.lock;\
	fi
	@if [ -d vendor ]; then\
		printf $(_FMT) "rm -r vendor";\
						rm -r vendor;\
	fi

.PHONY: composer-install
composer-install:
	@printf $(_FMT) "$(COMPOSER_CMD) install --no-progress --prefer-dist --no-suggest"
	@                $(COMPOSER_CMD) install --no-progress --prefer-dist --no-suggest

.PHONY: composer-validate
composer-validate:
	@printf $(_FMT) "$(COMPOSER_CMD) validate"
	@                $(COMPOSER_CMD) validate
###< composer tasks ###

###> php-cs-fixer tasks ###
PHP_CS_FIXER_VERSION=2.13
PHP_CS_FIXER_CMD=docker run $(DOCKER_RUN_OPTS) $(DOCKER_ORGS)/php-cs-fixer:$(PHP_CS_FIXER_VERSION)-php$(PHP_VERSION) --ansi fix -vvv

.PHONY: php-cs-fixer-dry-run
php-cs-fixer-dry-run:
	@printf $(_FMT) "$(PHP_CS_FIXER_CMD) --dry-run"
	@                $(PHP_CS_FIXER_CMD) --dry-run

.PHONY: php-cs-fixer
php-cs-fixer:
	@printf $(_FMT) "$(PHP_CS_FIXER_CMD)"
	@                $(PHP_CS_FIXER_CMD)
###< php-cs-fixer tasks ###

###> php-parallel-lint tasks ###
PHP_PARALLEL_LINT_VERSION=1.0
PHP_PARALLEL_LINT_CMD=docker run $(DOCKER_RUN_OPTS) $(DOCKER_ORGS)/php-parallel-lint:$(PHP_PARALLEL_LINT_VERSION)-php$(PHP_VERSION) --exclude vendor .

.PHONY: php-parallel-lint
php-parallel-lint:
	@printf $(_FMT) "$(PHP_PARALLEL_LINT_CMD)"
	@                $(PHP_PARALLEL_LINT_CMD)
###< php-parallel-lint tasks ###

###> phpmetrics tasks ###
PHPMETRICS_VERSION=2.3
PHPMETRICS_CMD=docker run $(DOCKER_RUN_OPTS) $(DOCKER_ORGS)/phpmetrics:$(PHPMETRICS_VERSION)-php$(PHP_VERSION) --report-html=reports/phpmetrics --exclude=vendor .

.PHONY: phpmetrics
phpmetrics:
	@printf $(_FMT) "$(PHPMETRICS_CMD)"
	@                $(PHPMETRICS_CMD)
###< phpmetrics tasks ###

###> phpstan tasks ###
PHPSTAN_VERSION=0.10
PHPSTAN_CMD=docker run $(DOCKER_RUN_OPTS) $(DOCKER_ORGS)/phpstan:$(PHPSTAN_VERSION)-php$(PHP_VERSION) --ansi analyse --no-progress

.PHONY: phpstan
phpstan:
	@printf $(_FMT) "$(PHPSTAN_CMD)"
	@                $(PHPSTAN_CMD)
###< phpstan tasks ###

###> phpunit tasks ###
PHPUNIT_CMD=docker run $(DOCKER_RUN_OPTS) $(DOCKER_ORGS)/php:$(PHP_VERSION) phpdbg -qrr vendor/bin/phpunit

.PHONY: phpunit
phpunit:
	@printf $(_FMT) "$(PHPUNIT_CMD) --coverage-html=reports/coverage/html"
	@                $(PHPUNIT_CMD) --coverage-html=reports/coverage/html
###< phpunit tasks ###
