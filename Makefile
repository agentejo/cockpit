DOCKER_REPOSITORY := cockpit
DOCKER_TAG := $(shell git describe --abbrev=0)

.PHONY: build

build:
	docker build -t $(DOCKER_REPOSITORY):$(DOCKER_TAG) .
	docker build -t $(DOCKER_REPOSITORY):latest .
