<?php   
defined('C5_EXECUTE') or die(_("Access Denied."));

// NOTE: WE DO NO VALIDATION HERE!!!
// MAKE SURE YOU VALIDATE THAT NOTHING IMPORTANT IS GETTING OVERWRITTEN BEFORE USING THIS!!!!!
class DesignerContentBlockGenerator {
	
	private $fields = array();
	
	private $handle;
	private $name;
	private $description;

	private $outpath;
	private $tplpath;

	public function add_static_field($static_content) {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'static',
			'static' => $static_content,
		);
	}

	public function add_textbox_field($label, $prefix = '', $suffix = '', $required = false, $maxlength = 0) {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'textbox',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'required' => $required,
			'maxlength' => empty($maxlength) ? 0 : $maxlength,
		);
	}
	
	public function add_textarea_field($label, $prefix = '', $suffix = '', $required = false) {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'textarea',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'required' => $required,
		);
	}
	
	public function add_image_field($label, $prefix = '', $suffix = '', $required = false, $link_type = 0, $link_target_blank = true, $show_alt_text = false, $sizing_type = 0, $width = 0, $height = 0) {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'image',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'required' => $required,
			'link' => $link_type,
			'target' => $link_target_blank,
			'alt' => $show_alt_text,
			'sizing' => $sizing_type,
			'width' => $width,
			'height' => $height,
		);
	}
	
	public function add_file_field($label, $prefix = '', $suffix = '', $required = false) {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'file',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'required' => $required,
		);
	}
	
	public function add_link_field($label, $prefix = '', $suffix = '', $required = false) {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'link',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'required' => $required,
		);
	}
	
	public function add_url_field($label, $prefix = '', $suffix = '', $required = false, $target_blank = true) {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'url',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'required' => $required,
			'target' => $target_blank,
		);
	}
	
	public function add_date_field($label, $prefix = '', $suffix = '', $required = false, $format = '') {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'date',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'required' => $required,
			'format' => empty($format) ? 'Y-m-d' : $format,
		);
	}
	
	public function add_select_field($label, $options, $required, $show_header = false, $header_text = '') {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'select',
			'label' => $label,
			'options' => explode("\n", str_replace("\r", '', trim($options))),
			'required' => $required,
			'showheader' => $show_header,
			'headertext' => $header_text,
		);
	}
	
	public function add_wysiwyg_field($label, $prefix = '', $suffix = '', $default = '') {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'wysiwyg',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'default' => $default,
		);
	}

	public function generate($handle, $name, $description = '') {
		$this->handle = $handle;
		$this->name = $name;
		$this->description = $description;
		$this->outpath = DIR_FILES_BLOCK_TYPES . "/{$handle}/";
		$this->tplpath = DIR_BASE . '/' . DIRNAME_PACKAGES . '/designer_content/generator_templates/';

		$this->create_block_directory();
		$this->generate_add_php();
		$this->generate_auto_js();
		$this->generate_controller_php();
		$this->generate_db_xml();
		$this->generate_edit_php();
		if ($this->has_wysiwyg()) {
			$this->generate_editor_config_php();
			$this->generate_editor_controls_php();
			$this->generate_editor_init_php();
		}
		$this->generate_icon_png();
		$this->generate_view_php();
	}
		
	
/*** GENERATORS ***/
	private function create_block_directory() {
		mkdir(rtrim($this->outpath, '/'));
	}
		
	private function generate_add_php() {
		//No replacements
		$filename = 'add.php';
		copy($this->tplpath.$filename, $this->outpath.$filename);
	}
	
	private function generate_auto_js() {
		$filename = 'auto.js';
		
		//Load template
		$template = file_get_contents($this->tplpath.$filename);
		
		//Replace validation rules
		$code = '';
		foreach ($this->fields as $field) {
			$field_label = $this->addslashes_single($field['label']);
			if ($field['type'] == 'textbox' && $field['required']) {
				$code .= "\tif (\$('#field_{$field['num']}_textbox_text').val() == '') {\n";
				$translated_error = $this->addslashes_single( t('Missing required text') );
				$code .= "\t\tccm_addError('{$translated_error}: {$field_label}');\n";
				$code .= "\t}\n\n";
			}
			
			if ($field['type'] == 'textarea' && $field['required']) {
				$code .= "\tif (\$('#field_{$field['num']}_textarea_text').val() == '') {\n";
				$translated_error = $this->addslashes_single( t('Missing required text') );
				$label = $this->addslashes_single($field['label']);
				$code .= "\t\tccm_addError('{$translated_error}: {$field_label}');\n";
				$code .= "\t}\n\n";
			}
			
			if ($field['type'] == 'image' && $field['required']) {
				$code .= "\tif (\$('#field_{$field['num']}_image_fID-fm-value').val() == '' || \$('#field_{$field['num']}_image_fID-fm-value').val() == 0) {\n";
				$translated_error = $this->addslashes_single( t('Missing required image') );
				$label = $this->addslashes_single($field['label']);
				$code .= "\t\tccm_addError('{$translated_error}: {$field_label}');\n";
				$code .= "\t}\n\n";
			}
			
			if ($field['type'] == 'file' && $field['required']) {
				$code .= "\tif (\$('#field_{$field['num']}_file_fID-fm-value').val() == '' || \$('#field_{$field['num']}_file_fID-fm-value').val() == 0) {\n";
				$translated_error = $this->addslashes_single( t('Missing required file') );
				$label = $this->addslashes_single($field['label']);
				$code .= "\t\tccm_addError('{$translated_error}: {$field_label}');\n";
				$code .= "\t}\n\n";
			}
			
			if ($field['type'] == 'link' && $field['required']) {
				$code .= "\tif (\$('input[name=field_{$field['num']}_link_cID]').val() == '' || \$('input[name=field_{$field['num']}_link_cID]').val() == 0) {\n";
				$translated_error = $this->addslashes_single( t('Missing required link') );
				$label = $this->addslashes_single($field['label']);
				$code .= "\t\tccm_addError('{$translated_error}: {$field_label}');\n";
				$code .= "\t}\n\n";
			}
			
			if ($field['type'] == 'url' && $field['required']) {
				$code .= "\tif (\$('input[name=field_{$field['num']}_link_url]').val() == '') {\n";
				$translated_error = $this->addslashes_single( t('Missing required URL') );
				$label = $this->addslashes_single($field['label']);
				$code .= "\t\tccm_addError('{$translated_error}: {$field_label}');\n";
				$code .= "\t}\n\n";
			}
			
			if ($field['type'] == 'date' && $field['required']) {
				$code .= "\tif (\$('input[name=field_{$field['num']}_date_value]').val() == '' || \$('input[name=field_{$field['num']}_date_value]').val() == 0) {\n";
				$translated_error = $this->addslashes_single( t('Missing required date') );
				$label = $this->addslashes_single($field['label']);
				$code .= "\t\tccm_addError('{$translated_error}: {$field_label}');\n";
				$code .= "\t}\n\n";
			}
			
			if ($field['type'] == 'select' && $field['required']) {
				$code .= "\tif (\$('select[name=field_{$field['num']}_select_value]').val() == '' || \$('select[name=field_{$field['num']}_select_value]').val() == 0) {\n";
				$translated_error = $this->addslashes_single( t('Missing required selection') );
				$label = $this->addslashes_single($field['label']);
				$code .= "\t\tccm_addError('{$translated_error}: {$field_label}');\n";
				$code .= "\t}\n\n";
			}
		}
		$token = '[[[GENERATOR_REPLACE_VALIDATIONRULES]]]';
		$template = str_replace($token, $code, $template);
		
		//Output file (if we have anything to put in it)
		if (!empty($code)) {
			file_put_contents($this->outpath.$filename, $template);
		}
	}
		
	private function generate_controller_php() {
		$filename = 'controller.php';
		//Load template
		$template = file_get_contents($this->tplpath.$filename);
		
		//Replace sub-templates (do this first so tokens inside the subtemplates get replaced properly later on)
			//Image helper function
			$include_helper = false;
			foreach ($this->fields as $field) {
				if ($field['type'] == 'image') {
					$include_helper = true;
					break;
				}
			}
			$code = $include_helper ? file_get_contents($this->tplpath.'controller_image_helper.php') : '';
			$token = '[[[GENERATOR_REPLACE_IMAGEHELPER]]]';
			$template = str_replace($token, $code, $template);

			//URL helper function
			$include_helper = false;
			foreach ($this->fields as $field) {
				if ($field['type'] == 'url' || ($field['type'] == 'image' && $field['link'] == 2)) {
					$include_helper = true;
					break;
				}
			}
			$code = $include_helper ? file_get_contents($this->tplpath.'controller_url_helper.php') : '';
			$token = '[[[GENERATOR_REPLACE_URLHELPER]]]';
			$template = str_replace($token, $code, $template);
		
			//WYSIWYG content helper
			$include_helper = false;
			foreach ($this->fields as $field) {
				if ($field['type'] == 'wysiwyg') {
					$include_helper = true;
					break;
				}
			}
			$code = $include_helper ? file_get_contents($this->tplpath.'controller_content_helper.php') : '';
			$token = '[[[GENERATOR_REPLACE_CONTENTHELPER]]]';
			$template = str_replace($token, $code, $template);
		//END sub-template replacement
		
		//Replace class properties
		$template = str_replace('[[[GENERATOR_REPLACE_CLASSNAME]]]', $this->controllername($this->handle), $template);
		$template = str_replace('[[[GENERATOR_REPLACE_TABLENAME]]]', $this->tablename($this->handle), $template);
		$template = str_replace('[[[GENERATOR_REPLACE_NAME]]]', $this->addslashes_single($this->name), $template);
		$template = str_replace('[[[GENERATOR_REPLACE_DESCRIPTION]]]', $this->addslashes_single($this->description), $template);
		
		//Replace getSearchableContent() function
		$code = '';
		$fieldcount = 0;
		foreach ($this->fields as $field) {
			if ($field['type'] == 'textbox') {
				$code .= "\t\t\$content[] = \$this->field_{$field['num']}_textbox_text;\n";
				$fieldcount++;
			}
			if ($field['type'] == 'textarea') {
				$code .= "\t\t\$content[] = \$this->field_{$field['num']}_textarea_text;\n";
				$fieldcount++;
			}
			if ($field['type'] == 'file') {
				$code .= "\t\t\$content[] = \$this->field_{$field['num']}_file_linkText;\n";
				$fieldcount++;
			}
			if ($field['type'] == 'date') {
				$code .= "\t\t\$content[] = date('{$field['format']}', \$this->field_{$field['num']}_date_value);\n";
				$fieldcount++;
			}
			if ($field['type'] == 'wysiwyg') {
				$code .= "\t\t\$content[] = \$this->field_{$field['num']}_wysiwyg_content;\n";
				$fieldcount++;
			}
			//Intentionally leaving out image alt text and link text (doesn't make sense for those to come up in search results)
		}
		if ($fieldcount == 1) {
			$code = str_replace('$content[] =', 'return', $code);
			$code = "\tpublic function getSearchableContent() {\n" . $code . "\t}\n";
		} else if ($fieldcount > 1) {
			$code = "\t\t\$content = array();\n" . $code . "\t\treturn implode(' - ', \$content);\n";
			$code = "\tpublic function getSearchableContent() {\n" . $code . "\t}\n";
		}
		$token = '[[[GENERATOR_REPLACE_GETSEARCHABLECONTENT]]]';
		$template = str_replace($token, $code, $template);
		
		//Replace view() function
		$code = '';
		$include_image_helper = false;
		foreach ($this->fields as $field) {
			if ($field['type'] == 'image') {
				$width = ($field['sizing'] > 0 && !empty($field['width'])) ? $field['width'] : 0;
				$height = ($field['sizing'] > 0 && !empty($field['height'])) ? $field['height'] : 0;
				$crop = ($field['sizing'] == 2) ? 'true' : 'false';
				$code .= "\t\t\$this->set('field_{$field['num']}_image', \$this->get_image_object(\$this->field_{$field['num']}_image_fID, {$width}, {$height}, {$crop}));\n";
			}
			if ($field['type'] == 'file') {
				$code .= "\t\t\$this->set('field_{$field['num']}_file', File::getByID(\$this->field_{$field['num']}_file_fID));\n";
			}
			if ($field['type'] == 'wysiwyg') {
				$code .= "\t\t\$this->set('field_{$field['num']}_wysiwyg_content', \$this->translateFrom(\$this->field_{$field['num']}_wysiwyg_content));\n";
			}
		}
		if (!empty($code)) {
			$code = "\tpublic function view() {\n" . $code . "\t}\n";
		}
		$token = '[[[GENERATOR_REPLACE_VIEW]]]';
		$template = str_replace($token, $code, $template);
		
		//Replace add() function
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'date') {
				$code .= "\t\t\$this->set('field_{$field['num']}_date_value', date('Y-m-d'));\n";
			}
			if ($field['type'] == 'wysiwyg') {
				if (!empty($field['default'])) {
					$code .= "\t\t\$field_{$field['num']}_default_content = '" . $this->addslashes_single($field['default']) . "';\n";
					$code .= "\t\t\$this->set('field_{$field['num']}_wysiwyg_content', \$field_{$field['num']}_default_content);\n";
				}
			}
		}
		if (!empty($code)) {
			$code = "\tpublic function add() {\n\t\t//Set default values for new blocks\n" . $code . "\t}\n";
		}
		$token = '[[[GENERATOR_REPLACE_ADD]]]';
		$template = str_replace($token, $code, $template);
		
		//Replace edit() function
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'image') {
				$code .= "\t\t\$this->set('field_{$field['num']}_image', (empty(\$this->field_{$field['num']}_image_fID) ? null : File::getByID(\$this->field_{$field['num']}_image_fID)));\n";
			}
			if ($field['type'] == 'file') {
				$code .= "\t\t\$this->set('field_{$field['num']}_file', (empty(\$this->field_{$field['num']}_file_fID) ? null : File::getByID(\$this->field_{$field['num']}_file_fID)));\n";
			}
			if ($field['type'] == 'wysiwyg') {
				$code .= "\t\t\$this->set('field_{$field['num']}_wysiwyg_content', \$this->translateFromEditMode(\$this->field_{$field['num']}_wysiwyg_content));\n";
			}
		}
		if (!empty($code)) {
			$code = "\tpublic function edit() {\n" . $code . "\t}\n";
		}
		$token = '[[[GENERATOR_REPLACE_EDIT]]]';
		$template = str_replace($token, $code, $template);
		
		//Replace save() function
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'image') {
				$code .= "\t\t\$args['field_{$field['num']}_image_fID'] = empty(\$args['field_{$field['num']}_image_fID']) ? 0 : \$args['field_{$field['num']}_image_fID'];\n";
				$code .= ($field['link'] == 1) ? "\t\t\$args['field_{$field['num']}_image_internalLinkCID'] = empty(\$args['field_{$field['num']}_image_internalLinkCID']) ? 0 : \$args['field_{$field['num']}_image_internalLinkCID'];\n" : '';
			}
			if ($field['type'] == 'file') {
				$code .= "\t\t\$args['field_{$field['num']}_file_fID'] = empty(\$args['field_{$field['num']}_file_fID']) ? 0 : \$args['field_{$field['num']}_file_fID'];\n";
			}
			if ($field['type'] == 'link') {
				$code .= "\t\t\$args['field_{$field['num']}_link_cID'] = empty(\$args['field_{$field['num']}_link_cID']) ? 0 : \$args['field_{$field['num']}_link_cID'];\n";
			}
			if ($field['type'] == 'date') {
				$code .= "\t\t\$args['field_{$field['num']}_date_value'] = empty(\$args['field_{$field['num']}_date_value']) ? null : Loader::helper('form/date_time')->translate('field_{$field['num']}_date_value', \$args);\n";
			}
			if ($field['type'] == 'wysiwyg') {
				$code .= "\t\t\$args['field_{$field['num']}_wysiwyg_content'] = \$this->translateTo(\$args['field_{$field['num']}_wysiwyg_content']);\n";
			}
		}
		if (!empty($code)) {
			$code = "\tpublic function save(\$args) {\n" . $code . "\t\tparent::save(\$args);\n\t}\n";
		}
		$token = '[[[GENERATOR_REPLACE_SAVE]]]';
		$template = str_replace($token, $code, $template);
		
		//Output file
		file_put_contents($this->outpath.$filename, $template);
	}
	
	private function generate_db_xml() {
		$filename = 'db.xml';
		
		//Load template
		$template = file_get_contents($this->tplpath.$filename);
		
		//Replace table name
		$template = str_replace('[[[GENERATOR_REPLACE_TABLENAME]]]', $this->tablename($this->handle), $template);
		
		//Replace Field definitions
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'textbox') {
				$code .= "\t\t<field name=\"field_{$field['num']}_textbox_text\" type=\"X\"></field>\n\n";
			}
			if ($field['type'] == 'textarea') {
				$code .= "\t\t<field name=\"field_{$field['num']}_textarea_text\" type=\"X\"></field>\n\n";
			}
			if ($field['type'] == 'image') {
				$code .= "\t\t<field name=\"field_{$field['num']}_image_fID\" type=\"I\"></field>\n";
				$code .= ($field['link'] == 1) ? "\t\t<field name=\"field_{$field['num']}_image_internalLinkCID\" type=\"I\"></field>\n" : '';
				$code .= ($field['link'] == 2) ? "\t\t<field name=\"field_{$field['num']}_image_externalLinkURL\" type=\"C\" size=\"255\"></field>\n" : '';
				$code .= $field['alt'] ? "\t\t<field name=\"field_{$field['num']}_image_altText\" type=\"C\" size=\"255\"></field>\n" : '';
				$code .= "\n";
			}
			if ($field['type'] == 'file') {
				$code .= "\t\t<field name=\"field_{$field['num']}_file_fID\" type=\"I\"></field>\n";
				$code .= "\t\t<field name=\"field_{$field['num']}_file_linkText\" type=\"C\" size=\"255\"></field>\n\n";
			}
			if ($field['type'] == 'link') {
				$code .= "\t\t<field name=\"field_{$field['num']}_link_cID\" type=\"I\"></field>\n";
				$code .= "\t\t<field name=\"field_{$field['num']}_link_text\" type=\"C\" size=\"255\"></field>\n\n";
			}
			if ($field['type'] == 'url') {
				$code .= "\t\t<field name=\"field_{$field['num']}_link_url\" type=\"C\" size=\"255\"></field>\n";
				$code .= "\t\t<field name=\"field_{$field['num']}_link_text\" type=\"C\" size=\"255\"></field>\n\n";
			}
			if ($field['type'] == 'date') {
				$code .= "\t\t<field name=\"field_{$field['num']}_date_value\" type=\"D\"></field>\n\n";
			}
			if ($field['type'] == 'select') {
				$code .= "\t\t<field name=\"field_{$field['num']}_select_value\" type=\"I\"><default value=\"0\" /></field>\n\n";
			}
			if ($field['type'] == 'wysiwyg') {
				$code .= "\t\t<field name=\"field_{$field['num']}_wysiwyg_content\" type=\"X2\"></field>\n\n";
			}
		}
		$token = '[[[GENERATOR_REPLACE_FIELDS]]]';
		$template = str_replace($token, $code, $template);
		
		//Output file
		file_put_contents($this->outpath.$filename, $template);
	}
	
	private function generate_edit_php() {
		$filename = 'edit.php';
		
		//Load template
		$template = file_get_contents($this->tplpath.$filename);
		
		//Replace html form fields
		$include_asset_library = false;
		$include_page_selector = false;
		$include_date_time = false;
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'textbox') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<?php echo \$form->text('field_{$field['num']}_textbox_text', \$field_{$field['num']}_textbox_text, array('style' => 'width: 95%;'" . ($field['maxlength'] > 0 ? ", 'maxlength' => '{$field['maxlength']}'" : '') . ")); ?>\n";
				$code .= "</div>\n\n";
			}

			if ($field['type'] == 'textarea') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<textarea id=\"field_{$field['num']}_textarea_text\" name=\"field_{$field['num']}_textarea_text\" rows=\"5\" style=\"width: 95%;\"><?php echo \$field_{$field['num']}_textarea_text; ?></textarea>\n";
				$code .= "</div>\n\n";
			}
			
			if ($field['type'] == 'image') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$translated_label = $this->addslashes_single( t('Choose Image') );
				$code .= "\t<?php echo \$al->image('field_{$field['num']}_image_fID', 'field_{$field['num']}_image_fID', '{$translated_label}', \$field_{$field['num']}_image); ?>\n";
				$include_asset_library = true;
				if ($field['link'] > 0 || $field['alt']) {
					$code .= "\n";
					$code .= "\t<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" style=\"width: 95%;\">\n";
					if ($field['link'] == 1) {
						$translated_label = t('Link to Page');
						$code .= "\t\t<tr>\n";
						$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_image_internalLinkCID\">{$translated_label}:</label>&nbsp;</td>\n";
						$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$ps->selectPage('field_{$field['num']}_image_internalLinkCID', \$field_{$field['num']}_image_internalLinkCID); ?></td>\n";
						$code .= "\t\t</tr>\n";
						$include_page_selector = true;
					}
					if ($field['link'] == 2) {
						$translated_label = t('Link to URL');
						$code .= "\t\t<tr>\n";
						$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_image_externalLinkURL\">{$translated_label}:</label>&nbsp;</td>\n";
						$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$form->text('field_{$field['num']}_image_externalLinkURL', \$field_{$field['num']}_image_externalLinkURL, array('style' => 'width: 100%;')); ?></td>\n";
						$code .= "\t\t</tr>\n";
					}
					if ($field['alt']) {
						$translated_label = t('Alt Text');
						$code .= "\t\t<tr>\n";
						$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_image_altText\">{$translated_label}:</label>&nbsp;</td>\n";
						$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$form->text('field_{$field['num']}_image_altText', \$field_{$field['num']}_image_altText, array('style' => 'width: 100%;')); ?></td>\n";
						$code .= "\t\t</tr>\n";
					}
					$code .= "\t</table>\n";
				}
				$code .= "</div>\n\n";
			}
			
			if ($field['type'] == 'file') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$translated_label = $this->addslashes_single( t('Choose File') );
				$code .= "\t<?php echo \$al->file('field_{$field['num']}_file_fID', 'field_{$field['num']}_file_fID', '{$translated_label}', \$field_{$field['num']}_file); ?>\n";
				$include_asset_library = true;
				$code .= "\t<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" style=\"width: 95%;\">\n";
				$code .= "\t\t<tr>\n";
				$translated_label = t('Link Text (or leave blank to use file name)');
				$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_file_linkText\">{$translated_label}:</label>&nbsp;</td>\n";
				$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$form->text('field_{$field['num']}_file_linkText', \$field_{$field['num']}_file_linkText, array('style' => 'width: 100%;')); ?></td>\n";
				$code .= "\t\t</tr>\n";
				$code .= "\t</table>\n";
				$code .= "</div>\n\n";
			}
			
			if ($field['type'] == 'link') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<?php echo \$ps->selectPage('field_{$field['num']}_link_cID', \$field_{$field['num']}_link_cID); ?>\n";
				$include_page_selector = true;
				$code .= "\t<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" style=\"width: 95%;\">\n";
				$code .= "\t\t<tr>\n";
				$translated_label = t('Link Text');
				$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_link_text\">{$translated_label}:</label>&nbsp;</td>\n";
				$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$form->text('field_{$field['num']}_link_text', \$field_{$field['num']}_link_text, array('style' => 'width: 100%;')); ?></td>\n";
				$code .= "\t\t</tr>\n";
				$code .= "\t</table>\n";
				$code .= "</div>\n\n";
			}
			
			if ($field['type'] == 'url') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" style=\"width: 95%;\">\n";
				$code .= "\t\t<tr>\n";
				$translated_label = t('Link to URL');
				$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_link_url\">{$translated_label}:</label>&nbsp;</td>\n";
				$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$form->text('field_{$field['num']}_link_url', \$field_{$field['num']}_link_url, array('style' => 'width: 100%;')); ?></td>\n";
				$code .= "\t\t</tr>\n";
				$code .= "\t\t<tr>\n";
				$translated_label = t('Link Text');
				$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_link_text\">{$translated_label}:</label>&nbsp;</td>\n";
				$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$form->text('field_{$field['num']}_link_text', \$field_{$field['num']}_link_text, array('style' => 'width: 100%;')); ?></td>\n";
				$code .= "\t\t</tr>\n";
				$code .= "\t</table>\n";
				$code .= "</div>\n\n";
			}
			
			if ($field['type'] == 'date') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<?php echo \$dt->date('field_{$field['num']}_date_value', \$field_{$field['num']}_date_value); ?>\n";
				$code .= "</div>\n\n";
				$include_date_time = true;
			}
			
			if ($field['type'] == 'select') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<?php\n";
				$code .= "\t\$options = array(\n";
				if ($field['showheader']) {
					$code .= "\t\t'0' => '" . $this->addslashes_single(htmlspecialchars($field['headertext'], ENT_QUOTES, APP_CHARSET)) . "',\n";
				}
				$i = 1;
				foreach ($field['options'] as $option) {
					$code .= "\t\t'{$i}' => '" . $this->addslashes_single(htmlspecialchars($option, ENT_QUOTES, APP_CHARSET)) . "',\n";
					$i++;
				}
				$code .= "\t);\n";
				$code .= "\techo \$form->select('field_{$field['num']}_select_value', \$options, \$field_{$field['num']}_select_value);\n";
				$code .= "\t?>\n";
				$code .= "</div>\n\n";
			}
			
			if ($field['type'] == 'wysiwyg') {
				$code .= "<div class=\"ccm-block-field-group\" id=\"ccm-editor-pane\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<?php \$this->inc('editor_init.php'); ?>\n";
				$code .= "\t<textarea id=\"field_{$field['num']}_wysiwyg_content\" name=\"field_{$field['num']}_wysiwyg_content\" class=\"advancedEditor ccm-advanced-editor\"><?php echo \$field_{$field['num']}_wysiwyg_content; ?></textarea>\n";
				$code .= "</div>\n\n";
			}
		}
		$token = '[[[GENERATOR_REPLACE_FIELDS]]]';
		$template = str_replace($token, $code, $template);
		
		//Replace helpers (if needed)
		$code = '';
		$code .= $include_asset_library ? "\$al = Loader::helper('concrete/asset_library');\n" : '';
		$code .= $include_page_selector ? "\$ps = Loader::helper('form/page_selector', 'designer_content');\n" : '';
		$code .= $include_date_time ? "\$dt = Loader::helper('form/date_time');\n" : '';
		$token = '[[[GENERATOR_REPLACE_HELPERLOADERS]]]';
		$template = str_replace($token, $code, $template);
		
		//Output file
		file_put_contents($this->outpath.$filename, $template);
	}
		
	private function generate_editor_config_php() {
		//No replacements
		$filename = 'editor_config.php';
		copy($this->tplpath.$filename, $this->outpath.$filename);
	}
	
	private function generate_editor_controls_php() {
		//No replacements
		$filename = 'editor_controls.php';
		copy($this->tplpath.$filename, $this->outpath.$filename);
	}
	
	private function generate_editor_init_php() {
		$filename = 'editor_init.php';
		
		//Load template
		$template = file_get_contents($this->tplpath.$filename);
		
		//Replace dom id of the textarea
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'wysiwyg') {
				$code .= "field_{$field['num']}_wysiwyg_content";
			}
		}
		$token = '[[[GENERATOR_REPLACE_EDITORID]]]';
		$template = str_replace($token, $code, $template);
		
		//Output file
		file_put_contents($this->outpath.$filename, $template);
	}

	private function generate_icon_png() {
		//No replacements
		$filename = 'icon.png';
		copy($this->tplpath.$filename, $this->outpath.$filename);
	}
	
	private function generate_view_php() {
		$filename = 'view.php';
		
		//Load template
		$template = file_get_contents($this->tplpath.$filename);
		
		//Replace html
		$code = '';
		$include_navigation_helper = false;
		foreach ($this->fields as $field) {
			
			if ($field['type'] == 'static') {
				$code .= $field['static'] . "\n\n";
			}

			if ($field['type'] == 'textbox') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_textbox_text)): ?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<?php echo htmlspecialchars(\$field_{$field['num']}_textbox_text, ENT_QUOTES, APP_CHARSET); ?>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}

			if ($field['type'] == 'textarea') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_textarea_text)): ?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<?php echo nl2br(htmlspecialchars(\$field_{$field['num']}_textarea_text, ENT_QUOTES, APP_CHARSET)); ?>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}

			if ($field['type'] == 'image') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_image)): ?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				if ($field['link'] == 1) {
					$code .= "\t<?php if (!empty(\$field_{$field['num']}_image_internalLinkCID)) { ?><a href=\"<?php echo \$nh->getLinkToCollection(Page::getByID(\$field_{$field['num']}_image_internalLinkCID), true); ?>\"><?php } ?>\n";
					$include_navigation_helper = true;
				}
				if ($field['link'] == 2) {
					$code .= "\t<?php if (!empty(\$field_{$field['num']}_image_externalLinkURL)) { ?><a href=\"<?php echo \$this->controller->valid_url(\$field_{$field['num']}_image_externalLinkURL); ?>\"" . ($field['target'] ? ' target="_blank"' : '') . "><?php } ?>\n";
				}
				$code .= "\t<img src=\"<?php echo \$field_{$field['num']}_image->src; ?>\" width=\"<?php echo \$field_{$field['num']}_image->width; ?>\" height=\"<?php echo \$field_{$field['num']}_image->height; ?>\" alt=\"" . ($field['alt'] ? "<?php echo \$field_{$field['num']}_image_altText; ?>" : '') . "\" />\n";
				if ($field['link'] == 2) {
					$code .= "\t<?php if (!empty(\$field_{$field['num']}_image_externalLinkURL)) { ?></a><?php } ?>\n";
				}
				if ($field['link'] == 1) {
					$code .= "\t<?php if (!empty(\$field_{$field['num']}_image_internalLinkCID)) { ?></a><?php } ?>\n";
				}
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}
			
			if ($field['type'] == 'file') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_file)): ?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<a href=\"<?php echo View::url('/download_file', \$field_{$field['num']}_file_fID, Page::getCurrentPage()->getCollectionID()); ?>\" class=\"file-<?php echo \$field_{$field['num']}_file->getExtension(); ?>\">\n";
				$code .= "\t\t<?php echo empty(\$field_{$field['num']}_file_linkText) ? \$field_{$field['num']}_file->getFileName() : htmlspecialchars(\$field_{$field['num']}_file_linkText, ENT_QUOTES, APP_CHARSET); ?>\n";
				$code .= "\t</a>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}
			
			if ($field['type'] == 'link') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_link_cID)): ?>\n";
				$code .= "\t<?php\n";
				$code .= "\t\$link_url = \$nh->getLinkToCollection(Page::getByID(\$field_{$field['num']}_link_cID), true);\n";
				$include_navigation_helper = true;
				$code .= "\t\$link_text = empty(\$field_{$field['num']}_link_text) ? \$link_url : htmlspecialchars(\$field_{$field['num']}_link_text, ENT_QUOTES, APP_CHARSET);\n";
				$code .= "\t?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<a href=\"<?php echo \$link_url; ?>\"><?php echo \$link_text; ?></a>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}
			
			if ($field['type'] == 'url') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_link_url)): ?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<a href=\"<?php echo \$this->controller->valid_url(\$field_{$field['num']}_link_url); ?>\"" . ($field['target'] ? ' target="_blank"' : '') . ">\n";
				$code .= "\t\t<?php echo empty(\$field_{$field['num']}_link_text) ? \$field_{$field['num']}_link_url : htmlspecialchars(\$field_{$field['num']}_link_text, ENT_QUOTES, APP_CHARSET); ?>\n";
				$code .= "\t</a>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}
			
			if ($field['type'] == 'date') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_date_value)): ?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<?php echo date('{$field['format']}', strtotime(\$field_{$field['num']}_date_value)); ?>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}
			
			if ($field['type'] == 'select') {
				$i = 1;
				foreach ($field['options'] as $option) {
					$code .= "<?php if (\$field_{$field['num']}_select_value == {$i}): ?>\n";
					$translated_comment = t('ENTER MARKUP HERE FOR CHOICE "%s"', $option);
					$code .= "\t<!-- {$translated_comment} -->\n";
					$code .= "<?php endif; ?>\n\n";
					$i++;
				}
			}

			if ($field['type'] == 'wysiwyg') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_wysiwyg_content)): ?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<?php echo \$field_{$field['num']}_wysiwyg_content; ?>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}
			
		}
		$token = '[[[GENERATOR_REPLACE_HTML]]]';
		$template = str_replace($token, $code, $template);
		
		//Replace helpers (if needed)
		$code = '';
		$code .= $include_navigation_helper ? "\$nh = Loader::helper('navigation');\n" : '';
		$token = '[[[GENERATOR_REPLACE_HELPERLOADERS]]]';
		$template = str_replace($token, $code, $template);
				
		//Output file
		file_put_contents($this->outpath.$filename, $template);
	}

	
/*** UTILITY FUNCTIONS ***/
	public static function tablename($handle) {
		return 'bt' . DesignerContentBlockGenerator::camelcase($handle);
	}
	
	public static function controllername($handle) {
		return DesignerContentBlockGenerator::camelcase($handle) . 'BlockController';
	}
	
	public static function camelcase($handle) {
		$th = Loader::helper('text');
		return $th->camelcase($handle);
	}
	
	private function addslashes_single($s) {
		//Escape single quotes and backslashes only (not double quotes) -- intended for insertion into js or php single-quoted string.
		$s = str_replace("\\", "\\\\", $s);
		$s = str_replace("'", "\\'", $s);
		return $s;
	}

	private function has_wysiwyg() {
		foreach ($this->fields as $field) {
			if ($field['type'] == 'wysiwyg') {
				return true;
			}
		}
		return false;
	}
	
}
