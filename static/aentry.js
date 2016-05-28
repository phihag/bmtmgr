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

return {
	create_el: create_el,
};
})();


var entry_rows = [];
function add_entry_row() {
	var container = document.querySelector('.aentries');
	var idx = entry_rows.length;
	var entry_row = uiu.create_el(container, 'div');
	entry_rows.push(entry_row);

	var discipline_label = uiu.create_el(container, 'label');
	var discipline_select = uiu.create_el(discipline_label, 'select', {
		'name': 'discipline_' + idx,
	});
	tournament_info.disciplines.forEach(function(d) {
		uiu.create_el(discipline_select, 'option', {
			'value': d.id,
		}, d.name);
	});
}

var tournament_info;
document.addEventListener('DOMContentLoaded', function() {
	var disciplines_json = document.querySelector('.aentries').getAttribute('data-tournament-info');
	tournament_info = JSON.parse(disciplines_json);

	add_entry_row();
});