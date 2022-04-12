dev:
dev: install serve

install:
	composer install
	yarn install
	yarn encore dev

serve:
	symfony serve
