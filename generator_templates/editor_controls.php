<?php defined('C5_EXECUTE') or die("Access Denied.");
$version_541 = version_compare(APP_VERSION, '5.4.2', '<');
?>

<?php if ($version_541): ?>
<div class="ccm-editor-controls">
<div class="ccm-editor-controls-right-cap">
<?php else: ?>
<div class="ccm-editor-controls-left-cap" <?php echo isset($editor_width) ? 'style="width: '.$editor_width.'px;"' : ''; ?>>
<div class="ccm-editor-controls-right-cap">
<div class="ccm-editor-controls">
<?php endif; ?>

<ul>
<li ccm-file-manager-field="rich-text-editor-image"><a class="ccm-file-manager-launch" onclick="ccm_chooseAsset = ccm_chooseAsset_tinymce; ccm_editorCurrentAuxTool='image'; setBookMark();return false;" href="#"><?php echo t('Add Image')?></a></li>
<li><a class="ccm-file-manager-launch" onclick="ccm_chooseAsset = ccm_chooseAsset_tinymce; ccm_editorCurrentAuxTool='file'; setBookMark();return false;" href="#"><?php echo t('Add File')?></a></li>
<?php  if (isset($mode) && $mode == 'full') {?>
<li><a href="#" onclick="ccm_selectSitemapNode = ccm_selectSitemapNode_tinymce; setBookMark();ccmEditorSitemapOverlay();"><?php echo t('Insert Link to Page')?></a></li>
<?php  } else {?>
<li><a href="<?php echo REL_DIR_FILES_TOOLS_REQUIRED?>/sitemap_overlay.php?sitemap_mode=select_page" onclick="ccm_selectSitemapNode = ccm_selectSitemapNode_tinymce; setBookMark();" class="dialog-launch" dialog-modal="false" ><?php echo t('Insert Link to Page')?></a></li>
<?php  } ?>
<li><a style="float: right" href="<?php echo View::url('/dashboard/settings')?>"><?php echo t('Customize Toolbar')?></a></li>
</ul>

</div>
</div>
<?php echo $version_541 ? '' : '</div>'; ?>

<div id="rich-text-editor-image-fm-display">
<input type="hidden" name="fType" class="ccm-file-manager-filter" value="<?php echo FileType::T_IMAGE?>" />
</div>

<div class="ccm-spacer">&nbsp;</div>

<script type="text/javascript">
ccm_activateFileSelectors();
</script>