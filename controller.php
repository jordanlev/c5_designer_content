<?php defined('C5_EXECUTE') or die(_("Access Denied."));

class DesignerContentPackage extends Package {
	
	protected $pkgHandle = 'designer_content';
	protected $appVersionRequired = '5.5.2';
	protected $pkgVersion = '3.2';
	
	public function getPackageName() {
		return t("Designer Content"); 
	}	
	
	public function getPackageDescription() {
		return t('Create custom content blocks via the dashboard.');
	}
	
	public function install() {
		$pkg = parent::install();
		$this->_upgrade($pkg);
	}
	
	public function upgrade() {
		$pkg = Package::getByHandle('designer_content');
		$this->_upgrade($pkg);
		parent::upgrade();
	}
	
	private function _upgrade(&$pkg) {
		Loader::model('single_page');
		
		$oldDashboardPage = Page::getByPath('/dashboard/pages/designer_content');
		if ($oldDashboardPage && is_object($oldDashboardPage) && $oldDashboardPage->getCollectionID()) {
			$oldDashboardPage->delete();
		}
		
		$newDashboardPage = Page::getByPath('/dashboard/blocks/designer_content');
		if (!$newDashboardPage || !is_object($newDashboardPage) || !$newDashboardPage->getCollectionID()) {
			$newDashboardPage = SinglePage::add('/dashboard/blocks/designer_content', $pkg);
		}
		
		$this->_setupDashboardIcon($newDashboardPage, 'icon-gift');
		
	}
	
	private function _setupDashboardIcon($page, $icon) {
		$cak = CollectionAttributeKey::getByHandle('icon_dashboard');
		if (is_object($cak)) {
			$page->setAttribute('icon_dashboard', $icon);
		}
	}
	
}
