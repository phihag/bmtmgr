PNGFILES = tournament.png

all: $(PNGFILES)

.png.:
	inkscape favicon.svg --export-png favicon.png

.svg.png:
	inkscape --export-png $@ $<

clean:
	rm -f ${PNGFILES}

.PHONY: all clean

.SUFFIXES: .png .svg