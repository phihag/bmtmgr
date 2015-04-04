"use strict";

$(function() {
    var root_path = $('body').attr('data-root-path');
    $.getJSON(root_path + 'club/?autocomplete=json', function(clubs) {
        var ac = $.map(clubs, function(c) {
            return c.text;
        });

        $('.login .club').autocomplete({
            source: ac,
        });
    });

});
