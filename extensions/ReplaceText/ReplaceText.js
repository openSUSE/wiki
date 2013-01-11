function invertSelections() {
	'use strict';

	var form = document.getElementById('choose_pages' ),
		num_elements = form.elements.length,
		i,
		cur_element;

	for (i = 0; i < num_elements; i++) {
		cur_element = form.elements[i];

		if (cur_element.type === "checkbox" && cur_element.id !== 'create-redirect' && cur_element.id !== 'watch-pages') {
			form.elements[i].checked = form.elements[i].checked !== true;
		}
	}
}
