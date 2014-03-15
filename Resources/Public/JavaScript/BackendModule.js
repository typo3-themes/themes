jQuery(document).ready(function() {
	jQuery('.t3-icon-document-open').click(function() {
		jQuery(this).parents('tr').removeClass('defaultValueIsSelected');
		jQuery(this).parents('tr').find('input[type=checkbox]').prop('checked', 'checked');
	});
	jQuery('.t3-icon-edit-delete').click(function() {
		jQuery(this).parents('tr').addClass('defaultValueIsSelected');
		jQuery(this).parents('tr').find('input[type="checkbox"]').removeAttr('checked');
	});
	jQuery('#saveIcon').click(function() {
		jQuery('#saveableForm').submit();
	});
});