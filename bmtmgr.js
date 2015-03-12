"use strict";
(function() {
var db;
var body;

// current state
var tournament_id;
var tournament_name;



function error(msg) {
    alert(msg);
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


function db_error() {
    error("Database operation failed");
}


function create_tournament(onsuccess) {
    var name = window.prompt("Tournament name");
    if (! name) {
        return;
    }
    var transaction = db.transaction(["tournaments"], "readwrite");
    var tournaments = transaction.objectStore("tournaments");
    transaction.onerror = db_error;
    transaction.oncomplete(event) {
        tournaments.add({"name": name});
        callback();
    }
}

function select_tournament() {
    var d = ui_dialog("select_tournament");
    var list = document.createElement("ul");
    // TODO list existing tournaments
    d.appendChild(list);

    d.appendChild(
        ui_button("Create a new tournament", create_tournament, "add")
    );
}


function init_db(db) {
    var objectStores = [
        {name: "tournaments", autoIncrement: true},
        {name: "players", keyPath: ["tournament_id", "id"]},
        {name: "disciplines", keyPath: ["tournament_id", "name"]}
    ];
    objectStores.forEach(function(os) {
        var req = db.createObjectStore(os.name, os);
        req.onerror = function() {
            error("Failed to initialize database");
        };
    });
}


function main() {
    var request = indexedDB.open("tournaments", 2);
    request.onerror = function(event) {
        error("Cannot open database; browser without IndexedDB support?");
    };
    request.onupgradeneeded = function(event) {
        init_db(event.target.result);
    };
    request.onsuccess = function(event) {
        db = event.target.result;
    };
}

document.addEventListener("DOMContentLoaded", function() {
    body = document.getElementsByTagName("body")[0];
    document.getElementById("no-tournament-error").addEventListener('click', select_tournament);
    document.getElementById("btn-select-tournament").addEventListener('click', select_tournament);
    main();
});

})();
