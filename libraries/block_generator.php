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

	public function add_text_field($label, $prefix = '', $suffix = '', $required = false) {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'text',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'required' => $required,
		);
	}
	
	public function add_image_field($label, $prefix = '', $suffix = '', $required = false, $width = 0, $height = 0) {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'image',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
			'required' => $required,
			'width' => $width,
			'height' => $height,
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
	
	public function add_wysiwyg_field($label, $prefix = '', $suffix = '') {
		$this->fields[] = array(
			'num' => count($this->fields) + 1,
			'type' => 'wysiwyg',
			'label' => $label,
			'prefix' => $prefix,
			'suffix' => $suffix,
		);
	}

	public function generate($handle, $name, $description = '', $package_version) {
		$this->handle = $handle;
		$this->name = $name;
		$this->description = $description;
		$this->outpath = DIR_FILES_BLOCK_TYPES . "/{$handle}/";
		$this->tplpath = DIR_BASE . '/' . DIRNAME_PACKAGES . '/designer_content/generator_templates/';

		$this->create_block_directory();
		$this->generate_add_php();
		$this->generate_auto_js();
		$this->generate_changelog($package_version);
		$this->generate_controller_php();
		$this->generate_db_xml();
		$this->generate_edit_php();
		$this->generate_editor_config_php();
		$this->generate_editor_controls_php();
		$this->generate_editor_init_php();
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
			if ($field['type'] == 'text' && $field['required']) {
				$code .= "\tif (\$('#field_{$field['num']}_textbox_text').val() == '') {\n";
				$code .= "\t\tccm_addError(ccm_t('text-required') + ': ".$this->addslashes_single($field['label'])."');\n";
				$code .= "\t}\n\n";
			}
			
			if ($field['type'] == 'image' && $field['required']) {
				$code .= "\tif (\$('#field_{$field['num']}_image_fID-fm-value').val() == '' || \$('#field_{$field['num']}_image_fID-fm-value').val() == 0) {\n";
				$code .= "\t\tccm_addError(ccm_t('image-required') + ': ".$this->addslashes_single($field['label'])."');\n";
				$code .= "\t}\n\n";
			}
			
			if ($field['type'] == 'link' && $field['required']) {
				$code .= "\tif (\$('input[name=field_{$field['num']}_link_cID]').val() == '' || \$('input[name=field_{$field['num']}_link_cID]').val() == 0) {\n";
				$code .= "\t\tccm_addError(ccm_t('link-required') + ': ".$this->addslashes_single($field['label'])."');\n";
				$code .= "\t}\n\n";
			}
		}
		$token = '[[[GENERATOR_REPLACE_VALIDATIONRULES]]]';
		$template = str_replace($token, $code, $template);
		
		//Output file
		file_put_contents($this->outpath.$filename, $template);
	}
	
	private function generate_changelog($package_version) {
		$filename = 'CHANGELOG';
		
		//Load template
		$template = file_get_contents($this->tplpath.$filename);
				
		//Replace html
		$code = $package_version;
		$token = '[[[GENERATOR_REPLACE_VERSION]]]';
		$template = str_replace($token, $code, $template);
		
		//Output file
		file_put_contents($this->outpath.$filename, $template);
	}
	
	private function generate_controller_php() {
		$filename = 'controller.php';
		//Load template
		$template = file_get_contents($this->tplpath.$filename);
		
		//Replace class properties
		$template = str_replace('[[[GENERATOR_REPLACE_CLASSNAME]]]', $this->controllername($this->handle), $template);
		$template = str_replace('[[[GENERATOR_REPLACE_TABLENAME]]]', $this->tablename($this->handle), $template);
		$template = str_replace('[[[GENERATOR_REPLACE_NAME]]]', $this->addslashes_single($this->name), $template);
		$template = str_replace('[[[GENERATOR_REPLACE_DESCRIPTION]]]', $this->addslashes_single($this->description), $template);
		
		//Replace getSearchableContent() function
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'text') {
				$code .= "\t\t\$content .= \$this->field_{$field['num']}_textbox_text;\n";
			}
			if ($field['type'] == 'wysiwyg') {
				$code .= "\t\t\$content .= \$this->field_{$field['num']}_wysiwyg_content;\n";
			}
			//Intentionally leaving out image alt text and link text (doesn't make sense for those to come up in search results)
		}
		$token = '[[[GENERATOR_REPLACE_GETSEARCHABLECONTENT]]]';
		$template = str_replace($token, $code, $template);

		//Replace view() function
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'image') {
				$width = empty($field['width']) ? 0 : $field['width'];
				$height = empty($field['height']) ? 0 : $field['height'];
				$code .= "\t\t\$this->set('field_{$field['num']}_image', \$this->get_image_object(\$this->field_{$field['num']}_image_fID, {$width}, {$height}));\n";
			}
			if ($field['type'] == 'wysiwyg') {
				$code .= "\t\t\$this->set('field_{$field['num']}_wysiwyg_content', \$this->translateFrom(\$this->field_{$field['num']}_wysiwyg_content));\n";
			}
		}
		$token = '[[[GENERATOR_REPLACE_VIEW]]]';
		$template = str_replace($token, $code, $template);
		
		//Replace add() function
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'image') {
				$code .= "\t\t\$this->set('field_{$field['num']}_image', (empty(\$this->field_{$field['num']}_image_fID) ? null : File::getByID(\$this->field_{$field['num']}_image_fID)));\n";
			}
		}
		$token = '[[[GENERATOR_REPLACE_ADD]]]';
		$template = str_replace($token, $code, $template);
		
		//Replace edit() function
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'image') {
				$code .= "\t\t\$this->set('field_{$field['num']}_image', (empty(\$this->field_{$field['num']}_image_fID) ? null : File::getByID(\$this->field_{$field['num']}_image_fID)));\n";
			}
			if ($field['type'] == 'wysiwyg') {
				$code .= "\t\t\$this->set('field_{$field['num']}_wysiwyg_content', \$this->translateFromEditMode(\$this->field_{$field['num']}_wysiwyg_content));\n";
			}
		}
		$token = '[[[GENERATOR_REPLACE_EDIT]]]';
		$template = str_replace($token, $code, $template);
		
		//Replace save() function
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'image') {
				$code .= "\t\t\$args['field_{$field['num']}_image_fID'] = (\$args['field_{$field['num']}_image_fID'] != '') ? \$args['field_{$field['num']}_image_fID'] : 0;\n";
			}
			if ($field['type'] == 'link') {
				$code .= "\t\t\$args['field_{$field['num']}_link_cID'] = (\$args['field_{$field['num']}_link_cID'] != '') ? \$args['field_{$field['num']}_link_cID'] : 0;\n";
			}
			if ($field['type'] == 'wysiwyg') {
				$code .= "\t\t\$args['field_{$field['num']}_wysiwyg_content'] = \$this->translateTo(\$args['field_{$field['num']}_wysiwyg_content']);\n";
			}
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
			if ($field['type'] == 'text') {
				$code .= "\t\t<field name=\"field_{$field['num']}_textbox_text\" type=\"X\"></field>\n\n";
			}
			if ($field['type'] == 'image') {
				$code .= "\t\t<field name=\"field_{$field['num']}_image_fID\" type=\"I\"></field>\n";
				$code .= "\t\t<field name=\"field_{$field['num']}_image_altText\" type=\"C\" size=\"255\"></field>\n\n";
				$code .= "\t\t<field name=\"field_{$field['num']}_image_externalLink\" type=\"C\" size=\"255\"></field>\n";
			}
			if ($field['type'] == 'link') {
				$code .= "\t\t<field name=\"field_{$field['num']}_link_cID\" type=\"I\"></field>\n";
				$code .= "\t\t<field name=\"field_{$field['num']}_link_text\" type=\"C\" size=\"255\"></field>\n\n";
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
		$code = '';
		foreach ($this->fields as $field) {
			if ($field['type'] == 'text') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<?php echo \$form->text('field_{$field['num']}_textbox_text', \$field_{$field['num']}_textbox_text, array('style' => 'width: 95%;')); ?>\n";
				$code .= "</div>\n\n";
			}
			
			if ($field['type'] == 'image') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<?php echo \$al->image('field_{$field['num']}_image_fID', 'field_{$field['num']}_image_fID', t('Choose Image'), \$field_{$field['num']}_image); ?>\n";
				$code .= "\n";
				$code .= "\t<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" style=\"width: 95%;\">\n";
				$code .= "\t\t<tr>\n";
				$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_image_altText\"><?php echo t('Alt Text'); ?>:</label>&nbsp;</td>\n";
				$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$form->text('field_{$field['num']}_image_altText', \$field_{$field['num']}_image_altText, array('style' => 'width: 100%;')); ?></td>\n";
				$code .= "\t\t</tr>\n";
				$code .= "\t\t<tr>\n";
				$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_image_externalLink\"><?php echo t('Link to URL'); ?>:</label>&nbsp;</td>\n";
				$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$form->text('field_{$field['num']}_image_externalLink', \$field_{$field['num']}_image_externalLink, array('style' => 'width: 100%;')); ?></td>\n";
				$code .= "\t\t</tr>\n";
				$code .= "\t</table>\n";
				$code .= "</div>\n\n";
			}
			
			if ($field['type'] == 'link') {
				$code .= "<div class=\"ccm-block-field-group\">\n";
				$code .= "\t<h2>{$field['label']}</h2>\n";
				$code .= "\t<?php echo \$ps->selectPage('field_{$field['num']}_link_cID', \$field_{$field['num']}_link_cID); ?>\n";
				$code .= "\t<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" style=\"width: 95%;\">\n";
				$code .= "\t\t<tr>\n";
				$code .= "\t\t\t<td align=\"right\" nowrap=\"nowrap\"><label for=\"field_{$field['num']}_link_text\"><?php echo t('Link Text'); ?>:</label>&nbsp;</td>\n";
				$code .= "\t\t\t<td align=\"left\" style=\"width: 100%;\"><?php echo \$form->text('field_{$field['num']}_link_text', \$field_{$field['num']}_link_text, array('style' => 'width: 100%;')); ?></td>\n";
				$code .= "\t\t</tr>\n";
				$code .= "\t</table>\n";
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
		foreach ($this->fields as $field) {
			
			if ($field['type'] == 'static') {
				$code .= $field['static'];
			}

			if ($field['type'] == 'text') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_textbox_text)): ?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<?php echo htmlspecialchars(\$field_{$field['num']}_textbox_text, ENT_QUOTES, APP_CHARSET); ?>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}

			if ($field['type'] == 'image') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_image)): ?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<?php if (!empty(\$field_{$field['num']}_image_externalLink)) { ?><a href=\"<?php echo \$field_{$field['num']}_image_externalLink; ?>\"><?php } ?>\n";
				$code .= "\t<img src=\"<?php echo \$field_{$field['num']}_image->src; ?>\" width=\"<?php echo \$field_{$field['num']}_image->width; ?>\" height=\"<?php echo \$field_{$field['num']}_image->height; ?>\" alt=\"<?php echo \$field_{$field['num']}_image_altText; ?>\" />\n";
				$code .= "\t<?php if (!empty(\$field_{$field['num']}_image_externalLink)) { ?></a><?php } ?>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
			}

			if ($field['type'] == 'link') {
				$code .= "<?php if (!empty(\$field_{$field['num']}_link_cID)): ?>\n";
				$code .= "\t<?php\n";
				$code .= "\t\$nh = Loader::helper('navigation');\n";
				$code .= "\t\$link_page = Page::getByID(\$field_{$field['num']}_link_cID);\n";
				$code .= "\t\$link_url = \$nh->getLinkToCollection(\$link_page, true);\n";
				$code .= "\t\$link_text = empty(\$field_{$field['num']}_link_text) ? \$link_url : htmlspecialchars(\$field_{$field['num']}_link_text, ENT_QUOTES, APP_CHARSET);\n";
				$code .= "\t?>\n";
				$code .= empty($field['prefix']) ? '' : "\t{$field['prefix']}\n";
				$code .= "\t<a href=\"<?php echo \$link_url; ?>\"><?php echo \$link_text; ?></a>\n";
				$code .= empty($field['suffix']) ? '' : "\t{$field['suffix']}\n";
				$code .= "<?php endif; ?>\n\n";
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
	
}