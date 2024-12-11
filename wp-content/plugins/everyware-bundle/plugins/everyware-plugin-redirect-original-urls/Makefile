## -*- makefile -*-

## https://www.navigaglobal.com/web/, Naviga Web Team

# The special rule .PHONY tells Make which targets are not files.
# This avoids conflict with files of the same name, and improves performance
.PHONY: composer test init

DOCKER_REPO = infomakerscandinaviaab
PHP_VERSION = 7.3.18

.ONESHELL:


# If the first argument is "run"...
ifeq (composer,$(firstword $(MAKECMDGOALS)))
  # use the rest as arguments for "run"
  COMPOSER_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  # ...and turn them into do-nothing targets
  $(eval $(COMPOSER_ARGS):;@:)
endif

composer:
	@docker run --rm -v $$(pwd):/app -v $$HOME/.ssh:/root/.ssh -v $$HOME/.composer:/root/.composer ${DOCKER_REPO}/php-fpm:${PHP_VERSION} composer $(COMPOSER_ARGS)

test:
	clear && bin/run-test.sh

init:
	bin/init.sh
