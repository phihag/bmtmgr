
function _ui_set_radio(form, name, new_value) {
    _ui_querySelectorAll(form, "input[type=radio]").forEach(function(inp) {
        if (inp.getAttribute("name") == name) {
            inp.checked = new_value == inp.getAttribute("value");
        }
    });
}

function _ui_querySelectorAll(node, q) {
    var lst = node.querySelectorAll(q);
    var res = []
    for (var i = 0;i < lst.length;i++) {
        res.push(lst[i]);
    }
    return res;
}

function _ui_dialog(css_class) {
    var dlg = document.createElement("dialog");
    dlg.setAttribute("class", css_class);
    // TODO close button

    var body = document.getElementsByTagName("body")[0];
    body.appendChild(dlg);
    dlg.showModal();
    dlg.addEventListener("close", function() {
        dlg.parentNode.removeChild(dlg);
    });
    return dlg;
}

function _ui_dialog_form(submit_label, submit_func, css_class) {
    var dlg = _ui_dialog(css_class);
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
            _ui_querySelectorAll(form, "input,textarea").forEach(function(inp) {
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

function _ui_button(label, handler, icon) {
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

function _ui_radio_group(name, values, attrs) {
    var container = document.createElement("div");
    values.forEach(function (v) {
        var label = document.createElement("label");
        var input = document.createElement("input");
        input.setAttribute("name", name);
        input.setAttribute("type", "radio");
        input.setAttribute("value", v);
        for (var k of attrs) {
            input.setAttribute(k, attrs[k]);
        }
        label.appendChild(input);
        label.appendChild(document.createTextNode(v));
        container.appendChild(label);
    });
    return container;
}

function _ui_set_text(node, text) {
    while (node.firstChild) {
        node.removeChild(node.firstChild);
    }
    node.appendChild(document.createTextNode(text));
}
