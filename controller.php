<?php defined('C5_EXECUTE') or die(_("Access Denied."));

class DesignerContentPackage extends Package {
	
	protected $pkgHandle = 'designer_content';
	protected $appVersionRequired = '5.5';
	protected $pkgVersion = '3.0';
	
	public function getPackageName() {
		return t("Designer Content"); 
	}	
	
	public function getPackageDescription() {
		return t('Provides supporting code for custom content blocks.');
	}
	
	public function install() {
		$pkg = parent::install();
		
		Loader::model('single_page');
		SinglePage::add('/dashboard/blocks/designer_content', $pkg);
	}
}
