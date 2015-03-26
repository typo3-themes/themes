jQuery(document).ready(function() {
	// toggle constant editor
	jQuery('.showEditForm').click(function() {
		jQuery(this).parents('tr').removeClass('defaultValueIsSelected');
		jQuery(this).parents('tr').find('input[type="hidden"]').attr('value', 'checked');
		jQuery(this).parents('tr').find('input[type="hidden"]').removeAttr('disabled');
	});
	jQuery('.hideEditForm').click(function() {
		jQuery(this).parents('tr').addClass('defaultValueIsSelected');
		jQuery(this).parents('tr').find('input[type="hidden"]').attr('value', '');
		jQuery(this).parents('tr').find('input[type="hidden"]').attr('disabled','disabled');

	});

	// make for submittable
	jQuery('#saveIcon').click(function() {
		jQuery('#saveableForm').submit();
	});

	// initialize Colorpicker
	jQuery('input.colorselector').each(function() {
		var inputElement = jQuery(this);
		jQuery(this).ColorPicker({
			onChange: function(hsb, hex, rgb, el) {
				jQuery(inputElement ).attr('value', '#' + hex);
				jQuery(inputElement).parent().find('.typo3-tstemplate-ceditor-colorblock').css('background-color', '#' + hex);
				jQuery(el).ColorPickerHide();
			},
			onBeforeShow: function () {
				jQuery(this).ColorPickerSetColor(this.value);
			}
		});
	});

	// init slider
	jQuery('.slider').unslider({
		keys: true, //  Enable keyboard (left, right) arrow shortcuts
		dots: true //  Display dot navigation
	});
	
	// constants quick search
	jQuery('#categoriesFilterSearchField').keyup(function() {
		categoriesFilterSearchDelay(function() {
			categoriesFilterSearch();
		}, 500);
	});
	jQuery('#categoriesFilterSearchScope').change(function() {
		categoriesFilterSearch();
	});
	jQuery('#categoriesFilterShowBasic').change(function() {
		categoriesFilterSearch();
	});
	jQuery('#categoriesFilterShowAdvanced').change(function() {
		categoriesFilterSearch();
	});
	jQuery('#categoriesFilterShowExpert').change(function() {
		categoriesFilterSearch();
	});

	// Filter initially
	categoriesFilterSearch();
});

var categoriesFilterSearchField = '';
var categoriesFilterSearchScope = '';
var categoriesFilterShowBasic = '';
var categoriesFilterShowAdvanced = '';
var categoriesFilterShowExpert = '';
var categoriesFilterSearchDelay = (function() {
	var timer = 0;
	return function(callback, ms) {
		clearTimeout (timer);
		timer = setTimeout(callback, ms);
	};
})();

function categoriesFilterSearch() {
	categoriesFilterSearchField = jQuery('#categoriesFilterSearchField').val();
	categoriesFilterSearchScope = jQuery('#categoriesFilterSearchScope').val();
	categoriesFilterShowBasic = jQuery('#categoriesFilterShowBasic').prop('checked');
	categoriesFilterShowAdvanced = jQuery('#categoriesFilterShowAdvanced').prop('checked');
	categoriesFilterShowExpert = jQuery('#categoriesFilterShowExpert').prop('checked');
	
	// Switch scope
	jQuery.each(jQuery("section.constants-group"), function(index, value) {
		if(categoriesFilterSearchScope == jQuery(value).attr('data-category') || categoriesFilterSearchScope == 'all') {
			jQuery(value).removeClass('hidden');
			jQuery(value).addClass('visible');
		}
		else {
			jQuery(value).addClass('hidden');
			jQuery(value).removeClass('visible');
		}
	});
	
	// User scope: basic, advanced, expert
	jQuery.each(jQuery("section.constants-group tbody tr"), function(index, value) {
		jQuery(value).addClass('hidden');
		jQuery(value).removeClass('visible');
		if(categoriesFilterShowBasic && jQuery(value).attr('data-userscope') == 'basic') {
			jQuery(value).removeClass('hidden');
			jQuery(value).addClass('visible');
		}  
		if(categoriesFilterShowAdvanced && jQuery(value).attr('data-userscope') == 'advanced') {
			jQuery(value).removeClass('hidden');
			jQuery(value).addClass('visible');
		}
		if(categoriesFilterShowExpert && jQuery(value).attr('data-userscope') == 'expert') {
			jQuery(value).removeClass('hidden');
			jQuery(value).addClass('visible');
		}
	});
	
	// Filter by search word, but only visible items
	jQuery.each(jQuery("section.constants-group tbody tr.visible"), function(index, value) {
		if(categoriesFilterSearchField != '') {
			var constantsKey = jQuery(value).find('td.title label').attr('for');
			var constantsTitle = jQuery(value).find('td.title label').html();
			jQuery(value).addClass('hidden');
			jQuery(value).removeClass('visible');
			if(constantsKey.indexOf(categoriesFilterSearchField) >= 0 || constantsTitle.indexOf(categoriesFilterSearchField) >= 0) {
				jQuery(value).removeClass('hidden');
				jQuery(value).addClass('visible');
			} 
			
		}
	});
	
	// Hide empty Tables
	jQuery.each(jQuery('section.constants-group.visible table'), function(index, value) {
		if(jQuery(value).find('tbody tr.hidden').length == jQuery(value).find('tbody tr').length) {
			jQuery(value).addClass('hidden');
			jQuery(value).removeClass('visible');
		}
		else {
			jQuery(value).removeClass('hidden');
			jQuery(value).addClass('visible');
		}
	});

	// Hide empty Headlines
	jQuery.each(jQuery('section.constants-group'), function(index, value) {
		if(jQuery(value).find('table.hidden').length == jQuery(value).find('table').length) {
			jQuery(value).addClass('hidden');
			jQuery(value).removeClass('visible');
		}
	});
}