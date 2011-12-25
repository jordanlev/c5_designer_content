<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

//Outputs "1" if handle is not in use by anything (blocks, packages, files, database)
//Outputs "2" if a corresponding database table exists for this handle but it is otherwise not in use (no blocks, packages, or files)
//Outputs nothing otherwise.

if (!empty($_GET['handle'])) {
	$handle = $_GET['handle'];
	
	$c = Loader::controller('/dashboard/blocks/designer_content');
	if ($c->validate_unique_handle($handle)) {
		if ($c->validate_unique_tablename_for_handle($handle)) {
			echo "1";
		} else {
			echo "2";
		}
	}
}
exit;
