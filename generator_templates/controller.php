<?php 
defined('C5_EXECUTE') or die("Access Denied.");

class [[[GENERATOR_REPLACE_CLASSNAME]]] extends BlockController {
	
	var $pobj;

	protected $btName = '[[[GENERATOR_REPLACE_NAME]]]';
	protected $btDescription = '[[[GENERATOR_REPLACE_DESCRIPTION]]]';
	protected $btTable = '[[[GENERATOR_REPLACE_TABLENAME]]]';
	protected $btInterfaceWidth = "700";
	protected $btInterfaceHeight = "450";
	
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = true;
	protected $btCacheBlockOutputOnPost = true;
	protected $btCacheBlockOutputForRegisteredUsers = true;
	protected $btCacheBlockOutputLifetime = 300;
	
[[[GENERATOR_REPLACE_GETSEARCHABLECONTENT]]]
[[[GENERATOR_REPLACE_VIEW]]]
	public function add() {
		//Set default values for new blocks
[[[GENERATOR_REPLACE_ADD]]]
	}
	
	public function edit() {
[[[GENERATOR_REPLACE_EDIT]]]
	}

	public function save($args) {
[[[GENERATOR_REPLACE_SAVE]]]
		parent::save($args);
	}

	public function getJavaScriptStrings() {
		return array(
			'text-required' => t('Missing required text'),
			'image-required' => t('Missing required image'),
			'file-required' => t('Missing required file'),
			'link-required' => t('Missing required link'),
			'url-required' => t('Missing required URL'),
		);
	}

[[[GENERATOR_REPLACE_IMAGEHELPER]]]
[[[GENERATOR_REPLACE_URLHELPER]]]
[[[GENERATOR_REPLACE_CONTENTHELPER]]]
}
