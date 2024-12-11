## -*- makefile -*-

## https://www.navigaglobal.com/web/, Naviga Web Team

# The special rule .PHONY tells Make which targets are not files.
# This avoids conflict with files of the same name, and improves performance
.PHONY: composer test init

.ONESHELL:

composer:
	docker run --rm -v `pwd`:/app composer composer install

test:
	clear && ./bin/run-test.sh

init:
	bin/init.sh
