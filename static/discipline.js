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
            var res = $.map(result.players, function(player) {
                return '(' + player.textid + ') ' + player.name + ' - ' + player.club_name;
            });
            response(res);
        }).error(response());
    }
    $(ps).autocomplete({
        minLength: 4,
        source: player_search,
        appendTo: ps.parentNode
    });
});
