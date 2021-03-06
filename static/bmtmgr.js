"use strict";

function get_disciplines() {
    return $('#disciplines_bar>a').map(function(_index, a) {
        return {
            id: a.getAttribute('data-discipline-id'),
            name: a.getAttribute('data-discipline-name')
        };
    });
}

$(function() {
    var root_path = $('body').attr('data-root-path');
    $.getJSON(root_path + 'club/?autocomplete=json', function(clubs) {
        var ac = $.map(clubs, function(c) {
            return c.text;
        });

        $('.club').autocomplete({
            source: ac,
        });
    });

    var root_path = $('body').attr('data-root-path');
    $.getJSON(root_path + 'user/?autocomplete=json', function(users) {
        var ac = $.map(users, function(u) {
            return u.text;
        });

        $('.user').autocomplete({
            source: ac,
        });
    });

    $('#discipline-goto input').on('keyup', function(e) {
        if (e.keyCode == 27) { // Esc
            $('#discipline-goto').hide();
        }
    });
    function goto_discipline_go() {
        function unify(s) {
            return s.toLowerCase().replace(/[-\su]/g, '');
        }
        var v = unify($('#discipline-goto input').val());
        $.each(get_disciplines(), function(i, d) {
            if (unify(d.name) == v) {
                $('#discipline-goto').hide();
                var m = window.location.href.match(/\/d\/[0-9]+\/(.*)$/);
                var subpage = m ? m[1] : '';
                window.location.href = root_path + 'd/' + d.id + '/' + subpage;
            }
        });
    }
    $('#discipline-goto').on('submit', goto_discipline_go);
    $('#discipline-goto input').on('change keyup', goto_discipline_go);
    function goto_discipline_show() {
        $('#discipline-goto').show();
        $('#discipline-goto input').val('');
        $('#discipline-goto input').focus();
        return false;
    }

    // Shortcuts
    Mousetrap.bind('g', goto_discipline_show);
    Mousetrap.bind('D', function() {
        $('#discipline_create').click();
    });
    Mousetrap.bind('ins', function() {
        $('#entry_add').click();
    });
    Mousetrap.bind('e', function() {
        $('#entry_add').click();
    });



});
