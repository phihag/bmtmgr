all: test
	$(MAKE) build

build:
	$(MAKE) -C libs all

t: test

test:
	phpunit

clean:
	$(MAKE) -C libs all

.PHONY: all build test t clean

