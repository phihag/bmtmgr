"use strict";
(function() {
var body;
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

function _set_text(node, text) {
    while (node.firstChild) {
        node.removeChild(node.firstChild);
    }
    node.appendChild(document.createTextNode(text));
}

function switch_tournament(new_name) {
    tournament = tournaments[new_name];
    document.getElementById("no-tournament-error").style.display = "none";
    document.getElementById("disciplines_disabled").style.display = "none";
    write_state();
    var name_field = document.getElementById("current-tournament-name");
    _set_text(name_field, new_name);
    if (tournament.disciplines) {
        ui_render_discipline_bar();
        var dkeys = Object.keys(tournament.disciplines).sort();
        if (dkeys.length > 0) {
            switch_discipline(dkeys[0]);
        }
    }
}

function switch_discipline(dname) {
    var active_btn = document.getElementById("disciplines_list_active_button");
    if (active_btn) {
        active_btn.setAttribute("id", "");
    }
    
    _querySelectorAll(document, "#disciplines_list>*").forEach(function (dbutton) {
            if (dbutton.getAttribute("data-discipline-name") == dname) {
            dbutton.setAttribute("id", "disciplines_list_active_button");
        }
    });
    discipline = dname;
    alert("TODO: switch discipline to " + dname);
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

function _set_radio(form, name, new_value) {
    _querySelectorAll(form, 'input[type=radio]').forEach(function(inp) {
        if (inp.getAttribute("name") == name) {
            inp.checked = new_value == inp.getAttribute("value");
        }
    });
}

function _querySelectorAll(node, q) {
    var lst = node.querySelectorAll(q);
    var res = []
    for (var i = 0;i < lst.length;i++) {
        res.push(lst[i]);
    }
    return res;
}

function ui_dialog(css_class) {
    var dlg = document.createElement("dialog");
    dlg.setAttribute("class", css_class);
    // TODO close button
    body.appendChild(dlg);
    dlg.showModal();
    dlg.addEventListener('close', function() {
        dlg.parentNode.removeChild(dlg);
    });
    return dlg;
}

function ui_dialog_form(submit_label, submit_func, css_class) {
    var dlg = ui_dialog(css_class);
    var form = document.createElement("form");
    var userspace = document.createElement("div")
    userspace.setAttribute("class", "dlg_form_userspace");
    form.appendChild(userspace);
    var submit_btn = document.createElement("input");
    submit_btn.setAttribute("type", "submit");
    submit_btn.setAttribute("value", submit_label);
    form.appendChild(submit_btn);
    form.addEventListener("submit", function(e) {
        e.preventDefault();
        try {
            var values = {};
            _querySelectorAll(form, 'input,textarea').forEach(function(inp) {
                if (inp.name) {
                    values[inp.name] = inp.value;
                }
            });
            submit_func(values);
            dlg.close();
        } catch (e) {
            console.error("error on form submission", e);
        }
        return false;
    });
    dlg.appendChild(form);

    return userspace;
}

function ui_button(label, handler, icon) {
    var btn = document.createElement("button");
    btn.setAttribute("tabindex", "0");
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

function ui_render_discipline_bar() {
    var disciplines = document.getElementById("disciplines_list");

    // Delete all current disciplines
    while (disciplines.firstChild) {
        disciplines.removeChild(disciplines.firstChild);
    }

    var dnames = Object.keys(tournament.disciplines);
    dnames.sort();
    dnames.forEach(function (dname) {
        var btn = ui_button(dname, function() {
            switch_discipline(dname);
        });
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

function ui_new_discipline() {
    var form = ui_dialog_form("Create discipline", function (values) {
        if (! tournament.disciplines) {
            tournament.disciplines = {};
        }
        tournament.disciplines[values.name] = {
            name: values.name,
            dtype: values.dtype,
            teams: []
        };
        console.log(tournaments);
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
            "MX": "doubles",
            "GD": "doubles",
            "HD": "doubles",
            "JD": "doubles",
            "DD": "doubles",
            "WD": "doubles",
            "HE": "singles",
            "JE": "singles",
            "MS": "singles",
            "DE": "singles",
            "ME": "singles",
            "WS": "singles"
        };
        Object.keys(NAME_TABLE).forEach(function(k) {
            if (name_input.value.indexOf(k) > -1) {
                _set_radio(form, "dtype", NAME_TABLE[k]);
            }
        });
    }
    name_input.addEventListener("change", guess_type);
    name_input.addEventListener("keyup", guess_type);
    name_label.appendChild(name_input);
    form.appendChild(name_label);
    name_input.focus();

    var dtype_div = document.createElement("div");
    dtype_div.setAttribute("class", "dlg_radio_group");
    ["singles", "doubles"].forEach(function (dtype) {
        var dtype_label = document.createElement("label");
        var dtype_input = document.createElement("input");
        dtype_input.setAttribute("class", "discipline_type");
        dtype_input.setAttribute("name", "dtype");
        dtype_input.setAttribute("type", "radio");
        dtype_input.setAttribute("required", "required");
        dtype_input.setAttribute("value", dtype);
        dtype_label.appendChild(dtype_input);
        dtype_label.appendChild(document.createTextNode(dtype));
        dtype_div.appendChild(dtype_label);
    });
    form.appendChild(dtype_div);
}


document.addEventListener("DOMContentLoaded", function() {
    body = document.getElementsByTagName("body")[0];
    document.getElementById("no-tournament-error").addEventListener('click', ui_select_tournament);
    document.getElementById("btn-select-tournament").addEventListener('click', ui_select_tournament);
    document.getElementById("btn-new-discipline").addEventListener('click', ui_new_discipline);

    init();

    if (! tournament) {
        ui_select_tournament();
    }
});

})();
