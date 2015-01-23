$(document).ready(function() {
	update_addfield_links();
	
	$('a.add-field-type').live('click', add_new_field);
	
	$('.designer-content-field-move-up a').live('click', move_field_up);
	$('.designer-content-field-move-down a').live('click', move_field_down);
	
	$('a.designer-content-field-delete').live('click', toggle_delete_confirmation);
	$('a.designer-content-field-delete-no').live('click', toggle_delete_confirmation);
	$('a.designer-content-field-delete-yes').live('click', delete_field);

	$('a.designer-content-field-duplicate').live('click', toggle_duplicate_confirmation);
	$('a.designer-content-field-duplicate-no').live('click', toggle_duplicate_confirmation);
	$('a.designer-content-field-duplicate-yes').live('click', duplicate_field);
	
	$('.designer-content-field-image-sizing-dropdown').live('change', toggle_field_image_sizing);
	$('.designer-content-field-image-link-dropdown').live('change', toggle_field_image_link);
	
	$('.designer-content-field-select-header').live('change', toggle_field_select_header);
	
	$('#designer-content-submit').click(function() {
		$('#designer-content-form').submit(); //We use a div instead of a submit button because we don't want the "enter" key triggering the form
	});
	$('#designer-content-form').submit(function() {
		// //TEST MODE (posts form via ajax so you don't lose data entry):
		// var valid = validate_form();
		// if (valid) {
		// 	$.ajax({
		// 		type: 'POST',
		// 		async: false,
		// 		url: CCM_REL + '/index.php/dashboard/blocks/designer_content/generate_block/',
		// 		data: $('#designer-content-form').serialize(),
		// 		success: function() {
		// 			alert('ok!');
		// 		}
		// 	});
		// }
		// return false;
		// //END TEST MODE
		
		$('#designer-content-submit').hide();
		$('#designer-content-submit-loading').show();
		var valid = validate_form(); //function will alert user to problems
		if (valid) {
			return true;
		} else {
			$('#designer-content-submit-loading').hide();
			$('#designer-content-submit').show();
			return false;
		}
	});
	
});

function update_addfield_links() {
	$("#add-field-types").html($("#add-field-types-template").tmpl());
}

function add_new_field() {
	var type = $(this).attr('data-type');
	if (type.length > 0) {
		var data = {
			'id': new_field_row_id(),
			'type': type,
			'label': FIELDTYPE_LABELS[type]
		};
		$("#field-template").tmpl(data).appendTo("#designer-content-fields").effect("highlight", {}, 500);
		update_addfield_links();
		update_move_links();
	}
	
	return false;
}

function new_field_row_id() {
	var max_id = 0;
	var cur_id = 0;
	$('.designer-content-field').each(function() {
		cur_id = parseInt($(this).attr('data-id'));
		max_id = (cur_id > max_id) ? cur_id : max_id;
	});
	return max_id + 1;
}

function update_move_links() {
	var is_first = true;
	var last_id = 0;
	var cur_id = 0;
	$('.designer-content-field').each(function() {
		cur_id = parseInt($(this).attr('data-id'));
		$('.designer-content-field-move-up[data-id='+cur_id+']').toggle(!is_first);
		$('.designer-content-field-move-down[data-id='+cur_id+']').show();
		is_first = false;
		last_id = cur_id;
	});
	$('.designer-content-field-move-down[data-id='+last_id+']').hide();
}

function move_field_up() {
	move_field($(this).attr('data-id'), true);
	return false;
}
function move_field_down() {
	move_field($(this).attr('data-id'), false);
	return false;
}
function move_field(data_id, up_vs_down) {
	var this_node = $('.designer-content-field[data-id='+data_id+']');
	var swap_node = up_vs_down ? this_node.prev('.designer-content-field') : this_node.next('.designer-content-field');
	swapNodes(this_node[0], swap_node[0]);
	
	this_node.effect("highlight", {}, 500);
	
	update_move_links();
}

function swapNodes(a, b) { /* from http://stackoverflow.com/questions/698301/#698440 */
    var aParent = a.parentNode;
    var aSibling = (a.nextSibling===b) ? a : a.nextSibling;
    b.parentNode.insertBefore(a, b);
    aParent.insertBefore(b, aSibling);
}

function toggle_delete_confirmation() {
	var id = $(this).attr('data-id');
	$('.designer-content-field-delete-confirm[data-id='+id+']').toggle();
	$('.designer-content-field-delete[data-id='+id+']').toggle();
	
	return false;
}

function delete_field() {
	var id = $(this).attr('data-id');
	$('.designer-content-field[data-id='+id+']').slideUp('fast', function() {
		$(this).remove();
		update_addfield_links();
		update_move_links();
	});
	
	return false;
}

function toggle_duplicate_confirmation() {
	var id = $(this).attr('data-id');
	$('.designer-content-field-duplicate-confirm[data-id='+id+']').toggle();
	$('.designer-content-field-duplicate[data-id='+id+']').toggle();
	
	return false;
}

function duplicate_field() {
	//add block of same type
	var type = $(this).attr('data-type');
	var parentId = $(this).attr('data-id')
	if (type.length > 0) {
		var data = {
			'id': new_field_row_id(),
			'type': type,
			'label': FIELDTYPE_LABELS[type]
		};
		$("#field-template").tmpl(data).appendTo("#designer-content-fields").effect("highlight", {}, 500);
		update_addfield_links();
		update_move_links();
	}
	//copy field values & append ' copy' to the block label
	if (type == 'static') {
		document.getElementById('fieldStaticHtml['+data.id+']').value = document.getElementById('fieldStaticHtml['+parentId+']').value
	}
	else {
		document.getElementById('fieldLabels['+data.id+']').value = document.getElementById('fieldLabels['+parentId+']').value + ' copy' 		
		if (type == 'select') {
				// 			$fields_required[$id]), $field_select_show_headers[$id], $field_select_header_texts[$id]);
			document.getElementById('fieldSelectOptions['+data.id+']').value = document.getElementById('fieldSelectOptions['+parentId+']').value
			document.getElementById('fieldsRequired['+data.id+']').value = document.getElementById('fieldsRequired['+parentId+']').value		
		}
		else {
			document.getElementById('fieldPrefixes['+data.id+']').value = document.getElementById('fieldPrefixes['+parentId+']').value
			document.getElementById('fieldSuffixes['+data.id+']').value = document.getElementById('fieldSuffixes['+parentId+']').value
			if (type == 'wysiwyg') {
				document.getElementById('fieldDefaultContents['+data.id+']').value = document.getElementById('fieldDefaultContents['+parentId+']').value
			}
			else{
				document.getElementById('fieldsRequired['+data.id+']').value = document.getElementById('fieldsRequired['+parentId+']').value
				if (type == 'textbox') {
					document.getElementById('fieldTextboxMaxlengths['+data.id+']').value = document.getElementById('fieldTextboxMaxlengths['+parentId+']').value
				}
				if (type == 'url') {
					document.getElementById('fieldUrlTargets['+data.id+']').value = document.getElementById('fieldUrlTargets['+parentId+']').value
				}
				if (type == 'date') {
					document.getElementById('fieldDateFormats['+data.id+']').value = document.getElementById('fieldDateFormats['+parentId+']').value
				}
				if (type == 'image') {
					document.getElementById('fieldImageShowAltTexts['+data.id+']').value = document.getElementById('fieldImageShowAltTexts['+parentId+']').value
					document.getElementById('fieldImageLinks['+data.id+']').value = document.getElementById('fieldImageLinks['+parentId+']').value
					document.getElementById('fieldImageLinkTargets['+data.id+']').value = document.getElementById('fieldImageLinkTargets['+parentId+']').value
					document.getElementById('fieldImageSizings['+data.id+']').value = document.getElementById('fieldImageSizings['+parentId+']').value
					document.getElementById('fieldImageWidths['+data.id+']').value = document.getElementById('fieldImageWidths['+parentId+']').value
					document.getElementById('fieldImageHeights['+data.id+']').value = document.getElementById('fieldImageHeights['+parentId+']').value
				}
			}
		} 
	}
}

function toggle_field_image_sizing() {
	var id = $(this).attr('data-id');
	var sizing = parseInt($(this).val());
	
	$('.designer-content-field-image-sizing-options[data-id='+id+']').toggle(sizing > 0);
	$('.designer-content-field-image-resize-label[data-id='+id+']').toggle(sizing == 1);	
	$('.designer-content-field-image-crop-label[data-id='+id+']').toggle(sizing == 2);	
}

function toggle_field_image_link() {
	var id = $(this).attr('data-id');
	var link = parseInt($(this).val());
	
	$('.designer-content-field-image-link-options[data-id='+id+']').toggle(link == 2);
}

function toggle_field_select_header() {
	var id = $(this).attr('data-id');
	var checked = $(this).is(':checked');
	$('.designer-content-field-select-header-text[data-id='+id+']').toggle(checked);
}

function validate_form() {
	//Name and handle are required
	//Handle must not already exist in the system (anywhere -- package, block, etc.)
	//Handle can only contain lowercase letters and underscores (note that for some reason, having numbers in the handle can totally mess things up -- any page that the block is on won't load (some error with the autoloader?).
	//must have at least 1 field
	//check that a label is provided for each field (except 'static html' fields)
	//check that width+height are valid integers if entered
	
	var errors = [];
	
	var name = $('#name').val();
	var handle = $('#handle').val();
	var fieldCount = $('.designer-content-field').length;
	var fieldLabels = $.map($('.designer-content-field-editorlabel'), function(element, index) { return $(element).val(); });
	var fieldImageWidths = $.map($('.designer-content-field-image-width'), function(element, index) { return $(element).val(); });
	var fieldImageHeights = $.map($('.designer-content-field-image-height'), function(element, index) { return $(element).val(); });
	var fieldSelectOptions = $.map($('.designer-content-field-select-options'), function(element, index) { return $(element).val(); });
	
	if (handle.length == 0) {
		errors.push(ERROR_MESSAGES['handle_required']);
	} else if (handle.length > 32) {
	    errors.push(ERROR_MESSAGES['handle_length']);
	} else if (!/^[a-z_]+$/.test(handle)) {
		errors.push(ERROR_MESSAGES['handle_lowercase']);
	} else if (!validate_handle(handle)) {
		errors.push(ERROR_MESSAGES['handle_exists']);
	}

	if (name.length == 0) {
		errors.push(ERROR_MESSAGES['name_required']);
	}
	
	if (fieldCount < 1) {
		errors.push(ERROR_MESSAGES['fields_required']);
	}
	
	var missing_labels = false;
	$.each(fieldLabels, function(index, label) {
		if (label.length == 0) {
			missing_labels = true;
		}
	});
	if (missing_labels) {
		errors.push(ERROR_MESSAGES['labels_required']);
	}

	var invalid_widths = false;
	$.each(fieldImageWidths, function(index, width) {
		if (width.length > 0 && (isNaN(width) || (width < 1) || (parseInt(width) != width))) {
			invalid_widths = true;
		}
	});
	if (invalid_widths) {
		errors.push(ERROR_MESSAGES['widths_numeric']);
	}
	
	var invalid_heights = false;
	$.each(fieldImageHeights, function(index, height) {
		if (height.length > 0 && (isNaN(height) || (height < 1) || (parseInt(height) != height))) {
			invalid_heights = true;
		}
	});
	if (invalid_heights) {
		errors.push(ERROR_MESSAGES['heights_numeric']);
	}
	
	var missing_options = false;
	$.each(fieldSelectOptions, function(index, options) {
		if (options.length == 0) {
			missing_options = true;
		}
	});
	if (missing_options) {
		errors.push(ERROR_MESSAGES['options_required']);
	}
	
	if (errors.length > 0) {
		alert(ERROR_MESSAGES['error_header'] + '\n * ' + errors.join('\n * '));
		return false;
	} else {
		return true;
	}
}

function validate_handle(handle) {
	var valid = false;
	$.ajax({
		'url': VALIDATE_HANDLE_URL,
		'method': 'get',
		'data': {'handle': handle},
		'async': false, //must be synchronous otherwise outer function returns before response is received from server
		success: function(response) {
			//dev note: call parseInt on the response because some servers are returning whitespace before/after the number
			if (parseInt(response) == 2) {
				valid = confirm(ERROR_MESSAGES['table_exists']);
			} else if (parseInt(response) == 1) {
				valid = true;
			}
		}
	});

	return valid;
}