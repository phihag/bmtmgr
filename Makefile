all:
	$(MAKE) build
	$(MAKE) test

build:
	$(MAKE) -C libs all
	$(MAKE) -C static/icons all

t: test

test:
	phpunit

clean:
	$(MAKE) -C libs clean
	$(MAKE) -C static/icons clean

.PHONY: all build test t clean

