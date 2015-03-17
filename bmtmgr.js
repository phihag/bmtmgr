"use strict";
(function() {
var tournaments = {};

// current state
var tournament = undefined;
var discipline = undefined;


function error(msg) {
    alert(msg);
}

function write_state() {
    window.localStorage.setItem("tournaments", JSON.stringify(tournaments));
    if (tournament) {
        window.localStorage.setItem("current_tournament", tournament.name);
    }
}

function switch_tournament(new_name) {
    tournament = tournaments[new_name];
    document.getElementById("no-tournament-error").style.display = "none";
    document.getElementById("disciplines_disabled").style.display = "none";
    write_state();
    var name_field = document.getElementById("current-tournament-name");
    _ui_set_text(name_field, new_name);
    ui_render_discipline_bar();
    switch_discipline("all-players");
}

function switch_discipline(dname) {
    _ui_querySelectorAll(document, ".disciplines_list_active_button").forEach(function(btn) {
        btn.setAttribute("class", "");
    });
    
    _ui_querySelectorAll(document, "#all-players,#disciplines_list>*").forEach(function (dbutton) {
        if (dbutton.getAttribute("data-discipline-name") == dname) {
            dbutton.setAttribute("class", "disciplines_list_active_button");
        }
    });
    discipline = dname;
    // TODO actually switch more of the UI here
}


function init() {
    var tournaments_json = window.localStorage.getItem("tournaments");
    if (tournaments_json) {
        tournaments = JSON.parse(tournaments_json);
        var cur = window.localStorage.getItem("current_tournament");
        if (cur) {
            switch_tournament(cur);
        }
    }
}

function ui_select_discipline_event(e) {
    var dname = e.target.getAttribute("data-discipline-name");
    switch_discipline(dname);
}

function ui_render_discipline_bar() {
    var disciplines = document.getElementById("disciplines_list");

    // Delete all current disciplines
    while (disciplines.firstChild) {
        disciplines.removeChild(disciplines.firstChild);
    }

    if (! tournament.disciplines) {
        return;
    }
    var dnames = Object.keys(tournament.disciplines);
    dnames.sort();
    dnames.forEach(function (dname) {
        var btn = _ui_button(dname, ui_select_discipline_event);
        btn.setAttribute("data-discipline-name", dname);
        disciplines.appendChild(btn);
    });
}

function ui_create_tournament(onsuccess) {
    var name = window.prompt("Tournament name");
    if (! name) {
        return;
    }
    if (name in tournaments) {
        error("tournament \"" + name + "\" already exists!");
        return;
    }
    tournaments[name] = {
        name: name,
        disciplines: {}
    };
    switch_tournament(name);
    return true;
}

function ui_select_tournament() {
    var d = _ui_dialog("dlg_select_tournament");
    var list = document.createElement("ul");
    if (tournaments) {
        for (var tname in tournaments) {
            var li = document.createElement("li");
            li.appendChild(document.createTextNode(tname));
            li.setAttribute("data-tournament-name", tname);
            var css_classes = "clickable";
            if (tournament && (tname == tournament.name)) {
                css_classes += " dlg_select_tournament_current";
            }
            li.setAttribute("class", css_classes);
            li.addEventListener("click", function(e) {
                var name = e.target.getAttribute("data-tournament-name");
                switch_tournament(name);
                d.close();
            });
            list.appendChild(li);
        }
    }
    d.appendChild(list);

    d.appendChild(
        _ui_button("Create a new tournament", function() {
            if (ui_create_tournament()) {
                d.close();
            }
        }, "add")
    );
}

function ui_new_discipline() {
    var form = _ui_dialog_form("Create discipline", function (values) {
        if (! tournament.disciplines) {
            tournament.disciplines = {};
        }
        tournament.disciplines[values.name] = {
            name: values.name,
            dtype: values.dtype,
            teams: []
        };
        write_state();
        ui_render_discipline_bar();
        switch_discipline(values.name);
    });

    var name_label = document.createElement("label");
    name_label.appendChild(document.createTextNode("Name "));
    var name_input = document.createElement("input");
    name_input.setAttribute("placeholder", "MX U19");
    name_input.setAttribute("required", "required");
    name_input.setAttribute("name", "name");
    function guess_type() {
        const NAME_TABLE = {
            "MX": "MX",
            "GD": "MX",
            "HD": "MD",
            "JD": "MD",
            "DD": "DD",
            "WD": "WD",
            "HE": "MS",
            "JE": "MS",
            "MS": "MS",
            "DE": "WS",
            "ME": "WS",
            "WS": "WS"
        };
        Object.keys(NAME_TABLE).forEach(function(k) {
            if (name_input.value.indexOf(k) > -1) {
                _ui_set_radio(form, "dtype", NAME_TABLE[k]);
            }
        });
    }
    name_input.addEventListener("change", guess_type);
    name_input.addEventListener("keyup", guess_type);
    name_label.appendChild(name_input);
    form.appendChild(name_label);
    name_input.focus();

    form.appendChild(_ui_radio_group(
        "dtype", ["MS", "WS", "MD", "WD", "MX"], {"required": "required"}));
}

function ui_add_player() {
    var f = _ui_dialog_form("Add player", function(values) {
        alert("Would add player now (" + JSON.stringify(values));
    });
    
    var name = document.createElement("div");
    var firstname = document.createElement("input");
    firstname.setAttribute("name", "firstname");
    firstname.setAttribute("placeholder", "First name");
    firstname.setAttribute("required", "required");
    name.appendChild(firstname);
    name.appendChild(document.createTextNode(" "));
    var surname = document.createElement("input");
    surname.setAttribute("name", "surname");
    surname.setAttribute("placeholder", "surname");
    surname.setAttribute("required", "required");
    name.appendChild(surname);
    f.appendChild(name);

    var email_label = document.createElement("label");
    email_label.appendChild(document.createTextNode("E-Mail: "));
    var email = document.createElement("input");
    email.setAttribute("type", "email");
    email.setAttribute("name", "email");
    email.setAttribute("placeholder", "email address (optional)");
    email.setAttribute("size", "38");
    email_label.appendChild(email);
    f.appendChild(email_label);
}



document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("no-tournament-error").addEventListener("click", ui_select_tournament);
    document.getElementById("btn-select-tournament").addEventListener("click", ui_select_tournament);
    document.getElementById("btn-new-discipline").addEventListener("click", ui_new_discipline);
    document.getElementById("all-players").addEventListener("click", ui_select_discipline_event);
    document.getElementById("btn-add-player").addEventListener("click", ui_add_player);
    Mousetrap.bind("ins", ui_add_player);
    init();

    if (! tournament) {
        ui_select_tournament();
    }
});

})();
