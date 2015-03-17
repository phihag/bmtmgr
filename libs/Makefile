all: download extract

download:
	test -e jquery.min.js || wget -O jquery.min.js 'http://code.jquery.com/jquery-1.11.2.min.js'
	test -e mousetrap.min.js || wget -O mousetrap.min.js 'http://cdn.craig.is/js/mousetrap/mousetrap.min.js?9d308'
	test -e jquery_ui.zip || wget -O jquery_ui.zip 'http://jqueryui.com/resources/download/jquery-ui-1.11.4.zip'
	test -e mustache.php || git clone https://github.com/bobthecow/mustache.php.git

extract:
	rm -rf jquery-ui-1.11.4
	unzip jquery_ui.zip
	ln -sf jquery-ui-1.11.4 jquery-ui

clean:
	rm -f *.js

.PHONY: all download clean

