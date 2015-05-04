"use strict";

function discipline_guess_dtype(name) {
    var firstChar = name.substr(0, 1);
    if (firstChar == ' ' || firstChar == '-') {
        return 'all';
    }
    var NAME_TABLE = {
        "MX": "MX",
        "GD": "MX",
        "XD": "MX",
        "HD": "MD",
        "JD": "MD",
        "DD": "WD",
        "WD": "WD",
        "HE": "MS",
        "JE": "MS",
        "MS": "MS",
        "DE": "WS",
        "ME": "WS",
        "WS": "WS"
    };
    for (var k in NAME_TABLE) {
        if (!NAME_TABLE.hasOwnProperty(k)) {
            continue;
        }

        if (name.indexOf(k) > -1) {
            return NAME_TABLE[name];
        }
    }
}

$(function() {
    $('.create_discipline input[name="name"]').on('change keyup', function() {
        var dtype = discipline_guess_dtype($(this).val());
        if (dtype) {
            $('.create_discipline input[name="dtype"][value="' + dtype + '"]').prop('checked', true);
        }
    });
});

$('.player_selector').each(function(index, ps) {
    function player_search(request, response) {
        var root_path = $('body').attr('data-root-path');
        var ac_url = root_path + 'd/' + ps.getAttribute('data-player_selector-discipline') + '/player_search';
        var ac_params = {
            gender: ps.getAttribute('data-player_selector-gender'),
            term: request.term
        };
        $.getJSON(ac_url, ac_params, function(result) {
            response(result.players);
        }).error(response());
    }

    function autocomplete_renderItem(ul, item) {
        var li = $("<li>");
        li.addClass('autocomplete_player');
        li.attr("data-player_textid", item.player_textid);
        li.attr("data-player_name", item.player_name);
        $('<span class="autocomplete_player_textid">')
            .text(item.textid)
            .appendTo(li);
        $('<span class="autocomplete_player_name">')
            .text(item.name)
            .appendTo(li);
        $('<span class="autocomplete_club_name">')
            .text(item.club_name)
            .appendTo(li);
        li.appendTo(ul);
        return li;
    }

    function autocomplete_set_value(ps, item) {
        $(ps).val('(' + item.textid + ') ' + item.name);
        var club_field = $(ps).parent().find('.club');
        club_field.val('(' + item.club_id + ') ' + item.club_name);
        club_field.addClass('club_selector_autoset');
    }

    $(ps).autocomplete({
        minLength: 3,
        source: player_search,
        select: function(event, ui) {
            autocomplete_set_value(ps, ui.item);
            event.preventDefault();

            var tabables = $('input:not([tabindex="-1"])');
            var current = $(':focus');
            var nextIndex = 0;
            if (current.length === 1) {
                var currentIndex = tabables.index(current);
                if (currentIndex + 1 < tabables.length){
                    nextIndex = currentIndex + 1;
                }
            }
            tabables.eq(nextIndex).focus();
        },
        focus: function(event, ui) {
            autocomplete_set_value(ps, ui.item);
            event.preventDefault(); 
        },
        response: function(event, ui) {
            if (ui.content.length == 1) {
                autocomplete_set_value(ps, ui.content[0]);
                $(this).autocomplete("close");
            }
        },

        appendTo: $(ps).parent()
    }).data("ui-autocomplete")._renderItem = autocomplete_renderItem;
});

$('.club').on('click focus', function () {
    $(this).removeClass('club_selector_autoset');
});