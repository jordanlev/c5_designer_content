<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

//Outputs "1" if handle is not already in use.
//Outputs nothing otherwise.

if (!empty($_GET['handle'])) {
	$handle = $_GET['handle'];
	
	$c = Loader::controller('/dashboard/pages/designer_content');
	if ($c->validate_unique_handle($handle)) {
		echo "1";
	}
}
exit;
