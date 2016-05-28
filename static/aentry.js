var uiu = (function() {
function create_el(parent, tagName, attrs, text) {
	var el = document.createElement(tagName);
	if (attrs) {
		for (var k in attrs) {
			el.setAttribute(k, attrs[k]);
		}
	}
	if ((text !== undefined) && (text !== null)) {
		el.appendChild(document.createTextNode(text));
	}
	parent.appendChild(el);
	return el;
}

function visible(el, val) {
	if (val) {
		el.style.display = 'block';
	} else {
		el.style.display = 'none';
	}
}

return {
	create_el: create_el,
	visible: visible,
};
})();

function discipline_by_id(did) {
	var disciplines = tournament_info.disciplines;
	for (var i = 0;i < disciplines.length;i++) {
		if (disciplines[i].id === did) {
			return disciplines[i];
		}
	}
	throw new Error('Could not find discipline ' + did);
}


function on_discipline_change(entry_idx, new_id, selection_container) {
	var discipline = discipline_by_id(new_id);
	var dtype = discipline.dtype;
	var current_rows = selection_container.querySelectorAll('.aentry_selection_row');
	var new_specs = discipline.specs;
	var new_count = new_specs.length;
	for (var i = 0;i < new_count;i++) {
		if (i < current_rows.length) {
			// TODO reconfigure gender here
			uiu.visible(current_rows[i], true);
		} else {
			// Create a new row
			add_player_selection_row(selection_container, entry_idx, i);
		}
	}
	for (var j = new_count;j < current_rows.length;j++) {
		uiu.visible(current_rows[j], false);
	}
}

function add_player_selection_row(container, entry_idx, row_idx) {
	var sel_row = uiu.create_el(container, 'div', {
		'class': 'aentry_selection_row',
	}, 'TODO: Select player ' + row_idx);

}


var entry_rows = [];
function add_entry_row() {
	var container = document.querySelector('.aentries');
	var idx = entry_rows.length;
	var entry_row = uiu.create_el(container, 'div');
	entry_rows.push(entry_row);

	var discipline_label = uiu.create_el(entry_row, 'label');
	var discipline_select = uiu.create_el(discipline_label, 'select', {
		'name': 'discipline_' + idx,
	});
	tournament_info.disciplines.forEach(function(d) {
		uiu.create_el(discipline_select, 'option', {
			'value': d.id,
		}, d.name);
	});
	discipline_select.addEventListener('change', function() {
		on_discipline_change(idx, discipline_select.value, selection_container);
	});

	var selection_container = uiu.create_el(entry_row, 'div');
	on_discipline_change(idx, discipline_select.value, selection_container);
}

var tournament_info;
document.addEventListener('DOMContentLoaded', function() {
	var disciplines_json = document.querySelector('.aentries').getAttribute('data-tournament-info');
	tournament_info = JSON.parse(disciplines_json);

	add_entry_row();
});