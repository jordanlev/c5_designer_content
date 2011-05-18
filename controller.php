<?php   
defined('C5_EXECUTE') or die(_("Access Denied."));

class DesignerContentPackage extends Package {
	
	protected $pkgHandle = 'designer_content';
	protected $appVersionRequired = '5.4.1';
	protected $pkgVersion = '1.0.1';
	
	public function getPackageName() {
		return t("Designer Content"); 
	}	
	
	public function getPackageDescription() {
		return t('A block type generator for designers and developers. Designer Content lets you quickly create new kinds of blocks that will make it easy for users to add pieces of content to their site (while maintaining complex layouts).');
	}

	public function on_start() {
		//NOTE: Don't use the on_page_view event because that doesn't get fired if "Track Page View Statistics" is turned off!
		Events::extend('on_before_render', 'DesignerContentPackage', 'on_before_render', DIR_PACKAGES . '/designer_content/controller.php');
	}
	
	public function on_before_render() {
		//Override system js IF user is in edit mode (or in the global scrapbook)
		if (Page::getCurrentPage()->isEditMode() || Page::getCurrentPage()->getCollectionPath() == '/dashboard/scrapbook') {
			$html = Loader::helper('html');
			$view = View::getInstance();
			$view->addHeaderItem($html->javascript(BASE_URL.DIR_REL.'/'.DIRNAME_PACKAGES.'/designer_content/js/ccm.filemanager.js'), 'CONTROLLER');
			//Note that we passed the 'CONTROLLER' namespace to addHeaderItem() so that it adds our items AFTER the core items
		}
	}

	public function install() {
		$pkg = parent::install();
		
		//Install dashboard page
		Loader::model('single_page');
		SinglePage::add('/dashboard/pages/designer_content', $pkg);
	}
	
}
