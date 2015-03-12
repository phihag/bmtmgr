"use strict";
(function() {
var body;
var tournaments = {};

// current state
var tournament = undefined;



function error(msg) {
    alert(msg);
}

function write_state() {
    window.localStorage.setItem("tournaments", JSON.stringify(tournaments));
    if (tournament) {
        window.localStorage.setItem("current_tournament", tournament.name);
    }
}

function _set_text(node, text) {
    while (node.firstChild) {
        node.removeChild(node.firstChild);
    }
    node.appendChild(document.createTextNode(text));
}

function switch_tournament(new_name) {
    tournament = tournaments[new_name];
    document.getElementById("no-tournament-error").style.display = "none";
    write_state();
    var name_field = document.getElementById("current-tournament-name");
    _set_text(name_field, new_name);
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


function ui_dialog(css_class) {
    var d = document.createElement("dialog");
    d.setAttribute("class", css_class);
    // TODO close button
    body.appendChild(d);
    d.showModal();
    return d;
}

function ui_button(label, handler, icon) {
    var btn = document.createElement("div");
    btn.setAttribute("class", "button");
    if (icon) {
        var img = document.createElement("img");
        img.setAttribute("src", "icons/" + icon + ".svg");
        img.setAttribute("title", label);
        btn.appendChild(img);
    }
    btn.addEventListener("click", handler);
    btn.appendChild(document.createTextNode(label));
    return btn;
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
        name: name
    };
    switch_tournament(name);
    return true;
}

function ui_select_tournament() {
    var d = ui_dialog("dlg_select_tournament");
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
            li.addEventListener('click', function(e) {
                var name = e.target.getAttribute("data-tournament-name");
                switch_tournament(name);
                d.close();
            });
            list.appendChild(li);
        }
    }
    d.appendChild(list);

    d.appendChild(
        ui_button("Create a new tournament", function() {
            if (ui_create_tournament()) {
                d.close();
            }
        }, "add")
    );
}


document.addEventListener("DOMContentLoaded", function() {
    body = document.getElementsByTagName("body")[0];
    document.getElementById("no-tournament-error").addEventListener('click', ui_select_tournament);
    document.getElementById("btn-select-tournament").addEventListener('click', ui_select_tournament);

    init();
});

})();
