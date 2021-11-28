.PHONY: analyze fix-code test test-with-integration coverage coverage-with-integration validation integration-test integration-coverage

analyze: | vendor
	$(COMPOSER) exec -v parallel-lint -- src
	$(COMPOSER) exec -v php-cs-fixer -- fix --dry-run
	$(COMPOSER) exec -v unused_scanner -- .unused.dist.php
	$(COMPOSER) exec -v security-checker -- security:check
	$(COMPOSER) exec -v phpcpd -- --fuzzy src
	$(COMPOSER) exec -v phpmd -- src ansi codesize,controversial,design,naming,unusedcode
	$(COMPOSER) exec -v phpa -- src
	$(COMPOSER) exec -v phpstan -- analyse
	$(COMPOSER) exec -v psalm -- src

fix-code: | vendor
	$(COMPOSER) normalize
	$(COMPOSER) exec -v php-cs-fixer -- fix

test: | vendor
	$(COMPOSER) exec -v phpunit

test-with-integration: | vendor
	$(COMPOSER) exec -v phpunit -- --group default,integration

coverage: | vendor
	@if [ -z "`php -v | grep -i 'xdebug'`" ]; then echo "You need to install Xdebug in order to do this action"; exit 1; fi
	XDEBUG_MODE=coverage $(COMPOSER) exec -v phpunit -- --coverage-text --color

coverage-with-integration: | vendor
	@if [ -z "`php -v | grep -i 'xdebug'`" ]; then echo "You need to install Xdebug in order to do this action"; exit 1; fi
	XDEBUG_MODE=coverage $(COMPOSER) exec -v phpunit -- --group default,integration --coverage-text --color

integration-test: | vendor
	$(COMPOSER) exec -v phpunit -- --group integration

integration-coverage: | vendor
	@if [ -z "`php -v | grep -i 'xdebug'`" ]; then echo "You need to install Xdebug in order to do this action"; exit 1; fi
	XDEBUG_MODE=coverage $(COMPOSER) exec -v phpunit -- --group integration --coverage-text --color

validation: fix-code analyze test-with-integration coverage-with-integration

vendor: composer.json
	$(COMPOSER) install --optimize-autoloader --no-suggest --prefer-dist
	touch vendor

composer.phar:
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php composer-setup.php --quiet
	rm composer-setup.php

# Check Composer installation
ifneq ($(shell command -v composer > /dev/null ; echo $$?), 0)
  ifneq ($(MAKECMDGOALS),composer.phar)
    $(shell $(MAKE) composer.phar)
  endif
  COMPOSER=php composer.phar
else
  COMPOSER=composer
endif