<?php defined('C5_EXECUTE') or die(_("Access Denied."));

class DesignerContentPackage extends Package {
	
	protected $pkgHandle = 'designer_content';
	protected $appVersionRequired = '5.5';
	protected $pkgVersion = '3.0.2';
	
	public function getPackageName() {
		return t("Designer Content"); 
	}	
	
	public function getPackageDescription() {
		return t('Provides supporting code for custom content blocks.');
	}
	
	public function install() {
		$pkg = parent::install();
		$this->_upgrade($pkg);
	}
	
	public function upgrade() {
		$pkg = Package::getByHandle('designer_content');
		$this->upgrade($pkg);
		parent::upgrade();
	}
	
	private function _upgrade(&$pkg) {
		Loader::model('single_page');
		
		$oldDashboardPage = Page::getByPath('/dashboard/pages/designer_content');
		if ($oldDashboardPage && is_object($oldDashboardPage) && $oldDashboardPage->getCollectionID()) {
			SinglePage::delete($oldDashboardPage);
		}
		
		$newDashboardPage = Page::getByPath('/dashboard/blocks/designer_content');
		if (!$newDashboardPage || !is_object($newDashboardPage) || !$oldDashboardPage->getCollectionID()) {
			SinglePage::add('/dashboard/blocks/designer_content', $pkg);
		}
		
	}
}
