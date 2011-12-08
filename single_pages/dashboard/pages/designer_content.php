<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>

<h1><span><?php echo t('Designer Content'); ?></span></h1>

<?php if ($is_generated): ?>

<div class="ccm-dashboard-inner">
	<?php echo t('Success!'); ?><br />
	<br />
	<?php echo t('%s has been installed, and will now be available in the "Add Blocks" list when pages are edited.', "<b>{$generated_name}</b>"); ?><br />
	<br />
	<a href="<?php echo View::url('/dashboard/pages/designer_content'); ?>"><?php echo t('Create another block type'); ?> &raquo;</a><br />
</div>

<?php else: ?>

<div class="ccm-dashboard-inner">
	<div>
		<h4>
			<?php echo t('Create a new block type by entering some basic info and adding one or more fields.'); ?>
			<span class="help-link">[<a href="http://www.concrete5.org/marketplace/addons/designer-content/documentation/" target="_blank"><?php echo t('Documentation'); ?></a>]</span>
			<span class="help-link">[<a href="http://www.concrete5.org/marketplace/addons/designer-content/forums/" target="_blank"><?php echo t('Help Forum'); ?></a>]</span>
		</h4>
	</div>

	<?php if (!$can_write): ?>
	<div id="write_permissions_warning">
		<?php echo t('Warning: The blocks directory is not writeable. Blocks cannot be created from this page until permissions are changed on your server.'); ?>
	</div>
	<?php endif; ?>


	<hr />

	<form method="post" action="<?php echo $this->action('generate_block'); ?>" id="designer-content-form">
	
		<table border="0" cellpadding="3" cellspacing="0">
			<tr>
				<td align="right"><h2><label for="handle"><?php echo t('Block Handle'); ?>:</label></h2></td>
				<td align="left">
					<?php echo $form->text('handle', $handle); ?>
					<i><?php echo t('lowercase letters and underscores only'); ?></i>
				</td>
			</tr>
			<tr>
				<td align="right"><h2><label for="name"><?php echo t('Block Name'); ?>:</label></h2></td>
				<td align="left">
					<?php echo $form->text('name', $name); ?>
					<i><?php echo t('human-readable name (appears in the "Add Block" list)'); ?></i>
				</td>
			</tr>
			<tr>
				<td align="right" valign="top">
					<h2><label for="description"><?php echo t('Block Description'); ?>:</label></h2>
					<span id="description-sublabel"><?php echo t('(optional)'); ?></span>
				</td>
				<td align="left" valign="top">
					<?php echo $form->textarea('description', $description, array('rows' => '3', 'cols' => '50')); ?>
					<span id="description-note"><?php echo t('for dashboard "Add Functionality" list'); ?></span>
				</td>
			</tr>
		</table>
	
		<hr />
	
		<div id="designer-content-fields">
			<script id="field-template" type="text/x-jQuery-tmpl">
	        <div class="designer-content-field" data-id="${id}" data-type="${type}">
				<input type="hidden" name="fieldIds[]" value="${id}" />
				<input type="hidden" name="fieldTypes[${id}]" value="${type}" />

				<div class="designer-content-field-header">
					<div class="designer-content-field-title">
						<b>${label}</b>
						&nbsp;
						[<a href="#" class="designer-content-field-delete" data-id="${id}"><?php echo t('delete'); ?></a><span class="designer-content-field-delete-confirm" data-id="${id}" style="display: none;">Are you sure? <a href="#" class="designer-content-field-delete-yes" data-id="${id}"><?php echo t('Yes'); ?></a> / <a href="#" class="designer-content-field-delete-no" data-id="${id}"><?php echo t('No'); ?></a></span>]
					</div>
					<div class="designer-content-field-move" data-id="${id}">
						<span class="designer-content-field-move-up" data-id="${id}">
						[<a href="#" data-id="${id}"><?php echo t('Move Up'); ?> &uarr;</a>]
						</span>

						&nbsp;&nbsp;

						<span class="designer-content-field-move-down" data-id="${id}">
						[<a href="#" data-id="${id}"><?php echo t('Move Down'); ?> &darr;</a>]
						</span>
					</div>
				</div>
			
				{{if type == 'static'}}
				<div class="designer-content-field-options static-html-field">
					<textarea rows="2" name="fieldStaticHtml[${id}]" id="fieldStaticHtml[${id}]"></textarea>
					<label><?php echo t('Anything entered here will be directly outputted to the block view &mdash; users will not be able to edit it.'); ?></label>
				</div>
				{{else}}

				<div class="designer-content-field-options">
					<label for="fieldLabels[${id}]"><?php echo t('Editor Label'); ?></label><br />
					<input type="text" class="designer-content-field-editorlabel" name="fieldLabels[${id}]" id="fieldLabels[${id}]" />
				
					{{if type == 'text' || type == 'link'}}
						<input type="checkbox" name="fieldsRequired[${id}]" id="fieldsRequired[${id}]" /><label for="fieldsRequired[${id}]"><?php echo t('Required?'); ?></label>
					{{else type == 'image'}}
						
						<br />
						
						<input type="checkbox" name="fieldsRequired[${id}]" id="fieldsRequired[${id}]" />
						<label for="fieldsRequired[${id}]"><?php echo t('Required?'); ?></label>

						<br />
						
						<table border="0" class="designer-content-field-image-settings"><tr><td nowrap="nowrap" align="right">
							<label for="fieldImageLinks[${id}]"><?php echo t('Show Link Field'); ?>:</label>
						</td><td nowrap="nowrap">
							<select name="fieldImageLinks[${id}]" id="fieldImageLinks[${id}]">
								<option value="0"><?php echo t('None'); ?></option>
								<option value="1"><?php echo t('Sitemap Page'); ?></option>
								<option value="2"><?php echo t('External URL'); ?></option>
							</select>
						</td></tr><tr><td nowrap="nowrap" align="right">
							<label for="fieldImagesShowAltText[${id}]"><?php echo t('Show Alt Text Field:'); ?></label>
						</td><td nowrap="nowrap">
							<select name="fieldImagesShowAltText[${id}]" id="fieldImagesShowAltText[${id}]">
								<option value="0"><?php echo t('No'); ?></option>
								<option value="1"><?php echo t('Yes'); ?></option>
							</select>
						</td></tr><tr><td nowrap="nowrap" align="right">
							<label for="fieldImageSizings[${id}]"><?php echo t('Image Sizing'); ?>:</label>
						</td><td nowrap="nowrap">
							<div class="designer-content-field-image-sizing">
								<select name="fieldImageSizings[${id}]" id="fieldImageSizings[${id}]" class="designer-content-field-image-sizing-dropdown" data-id="${id}">
									<option value="0"><?php echo t('Keep Original Size'); ?></option>
									<option value="1"><?php echo t('Resize Proportionally'); ?></option>
									<option value="2"><?php echo t('Resize+Crop To Fit'); ?></option>
								</select>
						
								<span style="display: none;" class="designer-content-field-image-sizes" data-id="${id}">
									&nbsp;&nbsp;
									<label for="fieldImageWidths[${id}]" class="designer-content-field-image-resize-label" data-id="${id}" style="display: none;"><?php echo t('Max Width'); ?>:</label>
									<label for="fieldImageWidths[${id}]" class="designer-content-field-image-crop-label" data-id="${id}" style="display: none;"><?php echo t('Target Width'); ?>:</label>
									<input type="text" name="fieldImageWidths[${id}]" id="fieldImageWidths[${id}]" class="designer-content-field-image-width" size="3" maxlength="4" /> px
									&nbsp;&nbsp;
									<label for="fieldImageHeights[${id}]" class="designer-content-field-image-resize-label" data-id="${id}"><?php echo t('Max Height'); ?>:</label>
									<label for="fieldImageHeights[${id}]" class="designer-content-field-image-crop-label" data-id="${id}"><?php echo t('Target Height'); ?>:</label>
									<input type="text" name="fieldImageHeights[${id}]" id="fieldImageHeights[${id}]" class="designer-content-field-image-height" size="3" maxlength="4" /> px
								</span>
							</div>
						</td></tr></table>
					{{else type == 'wysiwyg'}}
						<label for="fieldDefaultContents[${id}]"><?php echo t('Default HTML Content'); ?></label><br />
						<textarea rows="4" name="fieldDefaultContents[${id}]" id="fieldDefaultContents[${id}]"></textarea>
					{{/if}}
				</div>
				
				<div class="designer-content-field-html">
					<label for="fieldPrefixes[${id}]"><?php echo t('Wrapper HTML Open'); ?> <i>(&lt;div class="abc"&gt;)</i></label><br />
					<textarea rows="3" name="fieldPrefixes[${id}]" id="fieldPrefixes[${id}]"></textarea>
				</div>
				<div class="designer-content-field-html">
					<label for="fieldSuffixes[${id}]"><?php echo t('Wrapper HTML Close'); ?> <i>(&lt;/div&gt;)</i></label><br />
					<textarea rows="3" name="fieldSuffixes[${id}]" id="fieldSuffixes[${id}]"></textarea>
				</div>
				{{/if}}
	        </div>
		    </script>
		</div>
	
		<div id="designer-content-fields-add">
			<h2><?php echo t('Add Field');?>:</h2>
		
			<div id="add-field-types">
			</div>
			<script id="add-field-types-template" type="text/x-jQuery-tmpl">
				&nbsp;
				[<a href="#" class="add-field-type" data-type="static"><?php echo t('Static HTML'); ?></a>]
				&nbsp;&nbsp;
				[<a href="#" class="add-field-type" data-type="text"><?php echo t('Textbox'); ?></a>]
				&nbsp;&nbsp;
				[<a href="#" class="add-field-type" data-type="image"><?php echo t('Image'); ?></a>]
				&nbsp;&nbsp;
				[<a href="#" class="add-field-type" data-type="link"><?php echo t('Page Link'); ?></a>]
				{{if wysiwyg}}
				&nbsp;&nbsp;
				[<a href="#" class="add-field-type" data-type="wysiwyg"><?php echo t('WYSIWYG Editor'); ?></a>]
				{{/if}}
			</script>
		</div>

		<hr />
		
		<?php if ($can_write): ?>
		<div id="designer-content-submit-wrapper">
			<div id="designer-content-submit" class="button white">
				<?php echo t('Make The Block!'); ?>
			</div>
			<div id="designer-content-submit-loading" class="button white" style="display: none;">
				<?php echo t('Processing...'); ?>
			</div>
		</div>
		<?php endif; ?>
		
		<div id="designer-content-credits">
			<?php echo t('code + ui'); ?>: <a href="http://www.concrete5.org/profile/-/view/9756/" target="_blank">Jordan Lev</a>
			<br />
			<?php echo t('icon design'); ?>: <a href="http://kirkrobertsdesign.com/" target="_blank">Kirk Roberts</a>
		</div>

		<div style="clear: both;"></div>
		
	</form>

	<script type="text/javascript">
	var VALIDATE_HANDLE_URL = '<?php echo $validate_handle_url; ?>';

	//For translations (generated by php t() function):
	var FIELDTYPE_LABELS = {
		'static': '<?php echo t("Static HTML"); ?>',
		'text': '<?php echo t("Textbox Field"); ?>',
		'image': '<?php echo t("Image Field"); ?>',
		'link': '<?php echo t("Page Link Field"); ?>',
		'wysiwyg': '<?php echo t("WYSIWYG Editor"); ?>'
	};
	var ERROR_MESSAGES = {
		'name_required': '<?php echo t("Block Name is required."); ?>',
		'handle_required': '<?php echo t("Block Handle is required."); ?>',
		'handle_lowercase': '<?php echo t("Block Handle can only contain lowercase letters and underscores."); ?>',
		'handle_exists': '<?php echo t("Block Handle is already in use (either by another package, block type, or database table)."); ?>',
		'fields_required': '<?php echo t("You must add at least 1 field."); ?>',
		'one_wysiwyg': '<?php echo t("You cannot have more than 1 WYSIWYG Editor in a block."); ?>',
		'labels_required': '<?php echo t("All fields must have an Editor Label."); ?>',
		'widths_numeric': '<?php echo t("Image Widths must be valid numbers."); ?>',
		'heights_numeric': '<?php echo t("Image Heights must be valid numbers."); ?>',
		'error_header': '<?php echo t("Cannot proceed! Please correct the following errors:"); ?>'
	};
	</script>

</div>

<?php endif; ?>
