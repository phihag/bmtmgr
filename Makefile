all:
	$(MAKE) build
	$(MAKE) test

build:
	$(MAKE) -C libs all

t: test

test:
	phpunit

clean:
	$(MAKE) -C libs all

.PHONY: all build test t clean

