<?php   
defined('C5_EXECUTE') or die(_("Access Denied."));

class DashboardDesignerContentController extends Controller {
	
	public $helpers = array('form');

	function view() {
		$html = Loader::helper('html');
		$this->addHeaderItem($html->javascript('jquery.tmpl.min.js', 'designer_content'));
		$this->addHeaderItem($html->javascript('designer_content_dashboard_ui.js', 'designer_content'));
		$this->addHeaderItem($html->css('designer_content_dashboard_ui.css', 'designer_content'));
		
		$th = Loader::helper('concrete/urls'); 
		$this->set('validate_handle_url', $th->getToolsURL('validate_handle', 'designer_content'));
		
		$generated_handle = $this->get('generated');
		$generated_name = $this->block_name_for_handle($generated_handle);
		$this->set('is_generated', !empty($generated_handle));
		$this->set('generated_name', $generated_name);
		
		$this->set('can_write', is_writable(DIR_FILES_BLOCK_TYPES));
	}
	
	private function block_name_for_handle($handle) {
		if (empty($handle)) {
			return '';
		} else {
			$bt = BlockType::getByHandle($handle);
			return is_object($bt) ? $bt->getBlockTypeName() : '';
		}
	}
	
	public function generate_block() { //In single_pages, do not prepend "action_" (unlike blocks)
		//All validation happened in the front-end prior to submission.
		//But just in case... re-validate a few key things (especially that we're not going to overwrite something that already exists)
		$handle = $this->post('handle');
		$name = $this->post('name');
		if (!is_writable(DIR_FILES_BLOCK_TYPES)) {
			die(t('Error: Blocks directory is not writeable!'));
		} else if (empty($handle) || empty($name)) {
			die(t('Error: Block handle or name is missing!'));
		} else if (!$this->validate_unique_handle($handle)) {
			die(t('Error: Block Handle is already in use (either by another package, block type, or database table)!'));
		}
		
		//Gather all field data
		$field_ids = $this->post('fieldIds'); //The order of id's in this array reflects the user's chosen output order of the fields.
		$field_types = $this->post('fieldTypes');
		$field_labels = $this->post('fieldLabels');
		$field_prefixes = $this->post('fieldPrefixes');
		$field_suffixes = $this->post('fieldSuffixes');
		$field_static_html = $this->post('fieldStaticHtml');
		$fields_required = $this->post('fieldsRequired');
		$field_widths = $this->post('fieldWidths');
		$field_heights = $this->post('fieldHeights');
		
		//Set up the code generator
		Loader::library('block_generator', 'designer_content');
		$block = new DesignerContentBlockGenerator();
		foreach ($field_ids as $id) {
			$type = $field_types[$id];
			if ($type == 'static') {
				$block->add_static_field($field_static_html[$id]);
			} else if ($type == 'text') {
				$block->add_text_field($field_labels[$id], $field_prefixes[$id], $field_suffixes[$id], !empty($fields_required[$id]));
			} else if ($type == 'image') {
				$block->add_image_field($field_labels[$id], $field_prefixes[$id], $field_suffixes[$id], !empty($fields_required[$id]), $field_widths[$id], $field_heights[$id]);
			} else if ($type == 'link') {
				$block->add_link_field($field_labels[$id], $field_prefixes[$id], $field_suffixes[$id], !empty($fields_required[$id]));
			} else if ($type == 'wysiwyg') {
				$block->add_wysiwyg_field($field_labels[$id], $field_prefixes[$id], $field_suffixes[$id]);
			}
		}
		
		//Make+install block
		$pkg = Package::getByHandle('designer_content');
		$block->generate($handle, $name, $this->post('description'), $pkg->getPackageVersion());
		BlockType::installBlockType($handle);
		
		//Redirect back to view page so browser refresh doesn't trigger a re-generation
		header('Location: ' . View::url("/dashboard/designer_content/?generated={$handle}"));
		exit;
	}
	
	public function validate_unique_handle($handle) {
		$db = Loader::db();
		
		$pkg_exists = $db->GetOne("SELECT COUNT(*) FROM Packages WHERE pkgHandle = ?", array($handle));

		$block_exists = $db->GetOne("SELECT COUNT(*) from BlockTypes where btHandle = ?", array($handle));

		$dir_exists = is_dir(DIR_FILES_BLOCK_TYPES_CORE . '/' . $handle) || is_dir(DIR_FILES_BLOCK_TYPES . '/' . $handle);

		Loader::library('block_generator', 'designer_content');
		$tables = $db->MetaTables('TABLES');
		$table_name = DesignerContentBlockGenerator::tablename($handle);
		$table_exists = in_array($table_name, $tables);
			
		return (!$pkg_exists && !$block_exists && !$dir_exists && !$table_exists);
	}

}
