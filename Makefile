SHELL := /bin/bash

up:
	docker compose build php

shell:
	docker compose run --rm php bash

test:
	docker compose run --rm php composer test

demo:
	docker compose run --rm php php examples/demo.php
