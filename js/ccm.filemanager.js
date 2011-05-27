/* Designer Content Note:
 * Override this function in concrete's ccm.filemanager.js file.
 * We only need to change one thing -- the "Choose New Image" link (search code for "HACK" to find the change).
 */

ccm_alActivateMenu = function(obj, e) {
	
	// Is this a file that's already been chosen that we're selecting?
	// If so, we need to offer the reset switch
	
	var selectedFile = $(obj).find('div[ccm-file-manager-field]');
	var selector = '';
	if(selectedFile.length > 0) {
		selector = selectedFile.attr('ccm-file-manager-field');
	}
	ccm_hideMenus();
	
	var fID = $(obj).attr('fID');
	var searchInstance = $(obj).attr('ccm-file-manager-instance');

	// now, check to see if this menu has been made
	var bobj = document.getElementById("ccm-al-menu" + fID + searchInstance + selector);
	
	// This immediate click mode has promise, but it's annoying more than it's helpful
	/*
	if (ccm_alLaunchType != 'DASHBOARD' && selector == '') {
		// then we are in file list mode in the site, which means we 
		// we don't give out all the options in the list
		ccm_alSelectFile(fID);
		return;
	}
	*/
	
	if (!bobj) {
		// create the 1st instance of the menu
		el = document.createElement("DIV");
		el.id = "ccm-al-menu" + fID + searchInstance + selector;
		el.className = "ccm-menu";
		el.style.display = "none";
		document.body.appendChild(el);
		
		var filepath = $(obj).attr('al-filepath'); 
		bobj = $("#ccm-al-menu" + fID + searchInstance + selector);
		bobj.css("position", "absolute");
		
		//contents  of menu
		var html = '<div class="ccm-menu-tl"><div class="ccm-menu-tr"><div class="ccm-menu-t"></div></div></div>';
		html += '<div class="ccm-menu-l"><div class="ccm-menu-r">';
		html += '<ul>';
		if (ccm_alLaunchType[searchInstance] != 'DASHBOARD' && ccm_alLaunchType[searchInstance] != 'BROWSE') {
			// if we're launching this at the selector level, that means we've already chosen a file, and this should instead launch the library
			var onclick = (selectedFile.length > 0) ? 'ccm_alLaunchSelectorFileManager(\'' + selector + '\')' : 'ccm_alSelectFile(' + fID + ')';
//HACK: Fixes "TinyMCE editor prevents image control 'Choose New File' action from working":
			onclick = ((selectedFile.length > 0) ? 'ccm_chooseAsset=false;' : '') + onclick;
//END HACK
			var chooseText = (selectedFile.length > 0) ? ccmi18n_filemanager.chooseNew : ccmi18n_filemanager.select;
			html += '<li><a class="ccm-icon" dialog-modal="false" dialog-width="90%" dialog-height="70%" dialog-title="' + ccmi18n_filemanager.select + '" id="menuSelectFile' + fID + '" href="javascript:void(0)" onclick="' + onclick + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/add.png)">'+ chooseText + '<\/span><\/a><\/li>';
		}
		if (selectedFile.length > 0) {
			html += '<li><a class="ccm-icon" href="javascript:void(0)" id="menuClearFile' + fID + searchInstance + selector + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/remove.png)">'+ ccmi18n_filemanager.clear + '<\/span><\/a><\/li>';
		}
		
		if (ccm_alLaunchType[searchInstance] != 'DASHBOARD'  && ccm_alLaunchType[searchInstance] != 'BROWSE' && selectedFile.length > 0) {
			html += '<li class="header"></li>';	
		}
		if ($(obj).attr('ccm-file-manager-can-view') == '1') {
			html += '<li><a class="ccm-icon dialog-launch" dialog-modal="false" dialog-width="90%" dialog-height="75%" dialog-title="' + ccmi18n_filemanager.view + '" id="menuView' + fID + '" href="' + CCM_TOOLS_PATH + '/files/view?fID=' + fID + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/design_small.png)">'+ ccmi18n_filemanager.view + '<\/span><\/a><\/li>';
		} else {
			html += '<li><a class="ccm-icon" id="menuDownload' + fID + '" target="' + ccm_alProcessorTarget + '" href="' + CCM_TOOLS_PATH + '/files/download?fID=' + fID + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/design_small.png)">'+ ccmi18n_filemanager.download + '<\/span><\/a><\/li>';	
		}
		if ($(obj).attr('ccm-file-manager-can-edit') == '1') {
			html += '<li><a class="ccm-icon dialog-launch" dialog-modal="false" dialog-width="90%" dialog-height="75%" dialog-title="' + ccmi18n_filemanager.edit + '" id="menuEdit' + fID + '" href="' + CCM_TOOLS_PATH + '/files/edit?fID=' + fID + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/edit_small.png)">'+ ccmi18n_filemanager.edit + '<\/span><\/a><\/li>';
		}
		html += '<li><a class="ccm-icon dialog-launch" dialog-modal="false" dialog-width="680" dialog-height="450" dialog-title="' + ccmi18n_filemanager.properties + '" id="menuProperties' + fID + '" href="' + CCM_TOOLS_PATH + '/files/properties?searchInstance=' + searchInstance + '&fID=' + fID + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/wrench.png)">'+ ccmi18n_filemanager.properties + '<\/span><\/a><\/li>';
		if ($(obj).attr('ccm-file-manager-can-replace') == '1') {
			html += '<li><a class="ccm-icon dialog-launch" dialog-modal="false" dialog-width="300" dialog-height="250" dialog-title="' + ccmi18n_filemanager.replace + '" id="menuFileReplace' + fID + '" href="' + CCM_TOOLS_PATH + '/files/replace?searchInstance=' + searchInstance + '&fID=' + fID + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/paste_small.png)">'+ ccmi18n_filemanager.replace + '<\/span><\/a><\/li>';
		}
		if ($(obj).attr('ccm-file-manager-can-duplicate') == '1') {
			html += '<li><a class="ccm-icon" id="menuFileDuplicate' + fID + '" href="javascript:void(0)" onclick="ccm_alDuplicateFile(' + fID + ',\'' + searchInstance + '\')"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/add.png)">'+ ccmi18n_filemanager.duplicate + '<\/span><\/a><\/li>';
		}
		html += '<li><a class="ccm-icon dialog-launch" dialog-modal="false" dialog-width="500" dialog-height="400" dialog-title="' + ccmi18n_filemanager.sets + '" id="menuFileSets' + fID + '" href="' + CCM_TOOLS_PATH + '/files/add_to?searchInstance=' + searchInstance + '&fID=' + fID + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/window_new.png)">'+ ccmi18n_filemanager.sets + '<\/span><\/a><\/li>';
		if ($(obj).attr('ccm-file-manager-can-admin') == '1' || $(obj).attr('ccm-file-manager-can-delete') == '1') {
			html += '<li class="header"></li>';
		}
		if ($(obj).attr('ccm-file-manager-can-admin') == '1') {
			html += '<li><a class="ccm-icon dialog-launch" dialog-modal="false" dialog-width="400" dialog-height="380" dialog-title="' + ccmi18n_filemanager.permissions + '" id="menuFilePermissions' + fID + '" href="' + CCM_TOOLS_PATH + '/files/permissions?searchInstance=' + searchInstance + '&fID=' + fID + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/permissions_small.png)">'+ ccmi18n_filemanager.permissions + '<\/span><\/a><\/li>';
		}
		if ($(obj).attr('ccm-file-manager-can-delete') == '1') {
			html += '<li><a class="ccm-icon dialog-launch" dialog-modal="false" dialog-width="500" dialog-height="400" dialog-title="' + ccmi18n_filemanager.deleteFile + '" id="menuDeleteFile' + fID + '" href="' + CCM_TOOLS_PATH + '/files/delete?searchInstance=' + searchInstance + '&fID=' + fID + '"><span style="background-image: url(' + CCM_IMAGE_PATH + '/icons/delete_small.png)">'+ ccmi18n_filemanager.deleteFile + '<\/span><\/a><\/li>';
		}
		html += '</ul>';
		html += '</div></div>';
		html += '<div class="ccm-menu-bl"><div class="ccm-menu-br"><div class="ccm-menu-b"></div></div></div>';
		bobj.append(html);
		
		$("#ccm-al-menu" + fID + searchInstance + selector + " a.dialog-launch").dialog();
		
		$('a#menuClearFile' + fID + searchInstance + selector).click(function(e) {
			ccm_clearFile(e, selector);
			ccm_hideMenus();
		});

	} else {
		bobj = $("#ccm-al-menu" + fID + searchInstance + selector);
	}
	
	ccm_fadeInMenu(bobj, e);

}