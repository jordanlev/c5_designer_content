<?php 
/* Modified version of selectPage() function from concrete/helpers/form/page_selector.php
 *
 *Primary change: keep the page selector javascript from getting trounced by TinyMCE
 * (so they can both exist on the same page and still work -- otherwise page selections get inserted into
 * TinyMCE editing window instead of the page selector that was actually clicked on by the user).
 *
 * First we change the name of the ccm_selectSitemapNode js function so it's unique (different from both
 * the TinyMCE editor AND from other instances of the page selector),
 * then we add an onclick event to the page selector element so it resets the global ccm_selectSitemapNode
 * variable to our uniquely-named function before doing anything else.
 *
 *Other changes:
 *
 * Removed the third argument ($javascriptFunc) just to keep things simple (since we know we're not using it).
 *
 * Shuffled the page selector layout around so it's clearer, and added a "remove selection" option
 * so users can un-choose a page after they've already hosen one.
 */

defined('C5_EXECUTE') or die("Access Denied.");
class FormPageSelectorHelper {

	public function selectPage($fieldName, $cID = false) {
		$selectedCID = 0;
		if (isset($_REQUEST[$fieldName])) {
			$selectedCID = $_REQUEST[$fieldName];
		} else if ($cID > 0) {
			$selectedCID = $cID;
		}
		
		$unique_js_function_name = "ccm_selectSitemapNode_{$fieldName}";
		
		$html = '';
		$html .= '<div class="ccm-summary-selected-item"><div class="ccm-summary-selected-item-inner"><strong class="ccm-summary-selected-item-label">';
		if ($selectedCID > 0) {
			$oc = Page::getByID($selectedCID);
			$html .= $oc->getCollectionName();
		}
		$html .= '</strong></div>';
		$html .= '<a onclick="ccm_selectSitemapNode = ' . $unique_js_function_name . ';" class="ccm-sitemap-select-page" dialog-sender="' . $fieldName . '" dialog-width="90%" dialog-height="70%" dialog-modal="false" dialog-title="' . t('Choose Page') . '" href="' . REL_DIR_FILES_TOOLS_REQUIRED . '/sitemap_search_selector.php?sitemap_select_mode=select_page&cID=' . $selectedCID . '">' . t('Select Page') . '</a>';		$html .= '<input type="hidden" name="' . $fieldName . '" value="' . $selectedCID . '">';
		$html .= '</div>'; 
		$html .= '<script type="text/javascript"> 
		var ccmActivePageField;
		$(function() {
			$("a.ccm-sitemap-select-page").unbind();
			$("a.ccm-sitemap-select-page").dialog();
			$("a.ccm-sitemap-select-page").click(function() {
				ccmActivePageField = this;
			});
		});
		' . $unique_js_function_name . ' = function(cID, cName) { ';
		$html .= '
		var fieldName = $(ccmActivePageField).attr("dialog-sender");
		var par = $(ccmActivePageField).parent().find(\'.ccm-summary-selected-item-label\');
		var pari = $(ccmActivePageField).parent().find("[name=\'"+fieldName+"\']");
		par.html(cName);
		pari.val(cID);
		';
		$html .= "} \r\n </script>";
		return $html;
	}
	
}
